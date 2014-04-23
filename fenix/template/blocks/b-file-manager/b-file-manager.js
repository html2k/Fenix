Fx.required('b-popup');
Fx.required('b-dropdown');
Fx.getStyle('template/blocks/b-file-manager/b-file-manager.css');

;function bFileManager (){
    this.storage = {};

    this.option = {
        popup: {
            name: 'Файловый менеджер'
        },
        selfFolder: '/',
        changeCallback: []
    };

    this.init();
    this.bind();
}

bFileManager.prototype.init = function(){
    var that = this;
    $.ajax({
        type: 'POST',
        data: {
            action: 'loadTemplate',
            controller: 'bFileManager',
            templates: [
                'b-file-manager/b-file-manager.html',
                'b-file-manager/blocks/image-preview.html'
            ],
            pathTo: that.option.selfFolder
        },
        async: false,
        success: function(result){
            that.storage = result;
        }
    });
};

bFileManager.prototype.bind = function(){
    if(this.isInit){ return; }
    this.isInit = true;

    $(document)
        .on('mouseup', '.b-file-manager__folder', this.showFolder.bind(this))
        .on('keydown', '.b-file-manager__inp-create-folder', this.eventCreateFolder.bind(this))
        .on('mouseup', '.b-file-manager__btn-create-folder', this.createFolder.bind(this))
        .on('mouseup', '.b-file-manager__image', this.clickToItem.bind(this))
        .on('mouseup', '.b-file-manager__image-remove', this.removeItem.bind(this))
        .on('click', '.b-file-manager__btn-upload', this.eventClickUpload.bind(this))
    ;
};


bFileManager.prototype.show = function(){
    var that = this,
        template = _.template(that.storage.template['b-file-manager/b-file-manager.html']);

    that.option.popup.block = template();
    that.option.bPopup = bPopup.show(that.option.popup);

    that.storage.preview = that.option.bPopup.find('.b-file-manager__body-list');

    that.initUploadInput();
    that.bind();
    that.showPrewiev(true);

};

bFileManager.prototype.showPrewiev = function(is){
    var that = this;
    if(!is){
        $.ajax({
            type: 'POST',
            data: {
                action: 'loadTemplate',
                controller: 'bFileManager',
                pathTo: that.option.selfFolder
            },
            async: false,
            success: function(result){
                that.storage.file = result.file;
                that.storage.dir = result.dir;
            }
        });
    }
    var template = _.template(that.storage.template['b-file-manager/blocks/image-preview.html']);

    that.storage.preview.html(template({
        files: that.storage.file,
        dir: that.storage.dir,
        selfFolder: that.option.selfFolder
    }));
};


bFileManager.prototype.eventClickUpload = function(){
    this.option.bPopup.find('.b-file-manager__input-upload').click();
};


bFileManager.prototype.clickToItem = function(event){
    var element = $(event.target);

    if(element.hasClass('b-file-manager__image-remove') || element.closest('.b-file-manager__image-remove').legend){
        return;
    }

    if(!element.hasClass('b-file-manager__image')){
        element = element.parents('.b-file-manager__image:first')
    }
    var path = element.data('path'),
        size = element.data('size'),
        name = element.data('name');


    _.each(this.option.changeCallback, function(item){
        item(path, name, size);
    });
    bPopup.hide();
};


bFileManager.prototype.change = function(callback){
    if(_.isFunction(callback)){
        this.option.changeCallback.push(callback);
    }
};


bFileManager.prototype.initUploadInput = function(){
    var that = this,
        classLoad = 'btn__load',
        btn = $('.b-file-manager__btn-upload'),
        result = $('.b-file-manager__input-upload-result');

    this.option.bPopup.find('.b-file-manager__input-upload')
        .fileupload({
        url: '',
        formData:[
            {
                name: 'action',
                value: 'loadTemplate'

            },
            {
                name: 'controller',
                value: 'bFileManager'
            },
            {
                name: 'saveFile',
                value: '1'
            },
            {
                name: 'pathTo',
                value: that.option.selfFolder
            }
        ],
        dataType: 'json',
        start: function(){
            btn.addClass(classLoad);
        },
        done: function(e, data){

        },
        stop: function(){
            btn.removeClass(classLoad);
            that.showPrewiev();
        }
    });
};


bFileManager.prototype.removeItem = function(event){
    var that = this,
        element = event.target;
    if(element.hasAttribute('data-path')){
        $.ajax({
            type: 'POST',
            data: {
                action: 'loadTemplate',
                controller: 'bFileManager',
                removeItem: true,
                path: element.getAttribute('data-path'),
                pathTo: that.option.selfFolder
            },
            async: false,
            success: function(result){
                that.storage.file = result.file;
                that.storage.dir = result.dir;
                that.showPrewiev(true);
                that.initUploadInput();
            }
        });
    }
};


bFileManager.prototype.eventCreateFolder = function(event){
    if(event.keyCode === 13){
        this.createFolder();
    }
};


bFileManager.prototype.createFolder = function(){
    var that = this,
        folderName = $('.b-file-manager__inp-create-folder').val();
    if(folderName.length > 0){

        $.ajax({
            type: 'POST',
            data: {
                action: 'loadTemplate',
                controller: 'bFileManager',
                pathTo: that.option.selfFolder,
                createFolder: folderName
            },
            async: false,
            success: function(result){
                that.storage.file = result.file;
                that.storage.dir = result.dir;
                that.showPrewiev(true);
                bDropdown.hide();
            }
        });

    }
};


bFileManager.prototype.showFolder = function(event){
    var that = this,
        element = $(event.currentTarget),
        path = element.data('path');

    that.option.selfFolder = path;

    $.ajax({
        type: 'POST',
        data: {
            action: 'loadTemplate',
            controller: 'bFileManager',
            pathTo: that.option.selfFolder
        },
        async: false,
        success: function(result){
            that.storage.file = result.file;
            that.storage.dir = result.dir;

            that.showPrewiev(true);
            that.initUploadInput();
        }
    });
};


bFileManager.prototype.backUrl = function(url){
    return url.split('/').slice(0, -2).join('/') + '/';
};


bFileManager.prototype.getSrc = function(data){
    var types = {
        png: 'template/blocks/b-file-manager/image/image_file.png',
        jpg: 'template/blocks/b-file-manager/image/image_file.png',
        jpeg: 'template/blocks/b-file-manager/image/image_file.png',
        bmp: 'template/blocks/b-file-manager/image/image_file.png',
        icon: 'template/blocks/b-file-manager/image/image_file.png',
        exe: 'template/blocks/b-file-manager/image/exe.png',
        dmg: 'template/blocks/b-file-manager/image/dmg.png',
        mp3: 'template/blocks/b-file-manager/image/audio_file.png',
        audio: 'template/blocks/b-file-manager/image/audio_file.png',
        m4p: 'template/blocks/b-file-manager/image/audio_file.png',
        m4a: 'template/blocks/b-file-manager/image/audio_file.png',
        ai: 'template/blocks/b-file-manager/image/ai.png',
        pdf: 'template/blocks/b-file-manager/image/pdf.png',
        ppt: 'template/blocks/b-file-manager/image/powerpoint.png',
        psd: 'template/blocks/b-file-manager/image/psd.png',
        rar: 'template/blocks/b-file-manager/image/rar.png',
        gzip: 'template/blocks/b-file-manager/image/zip.png',
        zip: 'template/blocks/b-file-manager/image/zip.png',
        doc: 'template/blocks/b-file-manager/image/word.png',
        docx: 'template/blocks/b-file-manager/image/word.png',
        avi: 'template/blocks/b-file-manager/image/video_file.png',
        mp4: 'template/blocks/b-file-manager/image/video_file.png',
    };

    if(data.size < 799999 && data.system_type === 'image'){
        return data.path;
    }

    if(types[data.type]){
        return types[data.type];
    }else{
        return 'template/blocks/b-file-manager/image/file.png';
    }

};


window.bFileManager = new bFileManager();