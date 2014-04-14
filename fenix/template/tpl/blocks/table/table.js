//$(function(){
//	$('.js-edit').on('click', function(){
//        var $this = $(this),
//            $li = $this.parents('li'),
//            $name = $li.find('.name');
//
//        if(!$this.hasClass('dropdown_name')){
//            var $parent = $this.parents('.ibox'),
//                $form = $parent.find('.js-add').clone();
//            $this.addClass('dropdown_name');
//            $this.wrap('<span class="dropdown"/>');
//            $this.parent().append($form);
//            $form.removeClass('js-add').find('form').append('<input type="hidden" name="id" value="'+$li.data('id')+'"/>');
//        }
//
//        $li.find('.btn__positive .btn-in').text('Изменить');
//        $li.find('.inp').val($name.text());
//
//    });
//
//
//
//	var $tableBlock = $('.js-structTables'),
//        $findResult = $('.js-finde-result'),
//        $list = $tableBlock.find('ul > li'),
//        $table = $tableBlock.find('.js-structTable');
//
//
//    $list.eq(0).addClass('list-active_item');
//    $table.eq(0).removeClass('dn');
//
//});
//
//$(document).on('mouseup', '.js-getNextPage', function(){
//    var $this = $(this),
//        $table = $this.parents('.js-structTable').find('table'),
//        count = this.getAttribute('data-count'),
//        size = this.getAttribute('data-size') * 1,
//        table = this.getAttribute('data-table');
//
//    Spin.set($this.parent());
//    $.post('', {
//        action: 'loadTable',
//        count: count,
//        table: table
//    },function(response){
//        response = JSON.parse(response);
//
//        Notification.set({
//            message: response.message,
//            flag: 'good'
//        });
//        Notification.show();
//
//        var count = 0,
//            template = [];
//            template.push('<tr class="not"><th colspan="'+Object.keys(response.result[0]).length+'"><hr/></th></tr>');
//        while(true){
//            if(count == response.result.length){
//                break;
//            }
//
//            var obj = response.result[count];
//            template.push('<tr>');
//            template.push('<td><input type="checkbox" name="" value=""/></td>');
//            for(var i in obj){
//                template.push('<td>'+ obj[i] +'</td>');
//            }
//            template.push('<td><i class="icon-pencil"></i><i class="icon-cancel"></i></td>');
//            template.push('</tr>');
//            count++;
//        }
//
//        $table.append(template.join(''));
//        $this[0].setAttribute('data-count', response.count);
//
//
//        setTimeout(function(){
//            Spin.remove($this.parent());
//
//            if(size - response.count *1 < 0){
//                $this.remove();
//            }else{
//                $this.text('еще ' + (size - response.count *1))
//            }
//
//        }, 500);
//    });
//});
//
//$(document).on('mousedown', function(event){
//    if($(event.target).closest('.js-structTable .editable').length) return;
//    var $editable = $('.js-structTable .editable');
//    if($editable.length){
//        editableRow($editable[0]);
//        $editable.removeClass('editable');
//        $editable.attr('contenteditable', false);
//    }
//
//
//}).on('click', '.js-structTable td', function(){
//    if(this.className.indexOf('editable') > -1) return;
//
//    $('.js-structTable .editable').each(function (){
//        this.className = '';
//        this.setAttribute('contenteditable', false);
//    });
//
//    this.className = 'editable';
//    this.defaultValue = this.innerHTML;
//    this.setAttribute('contenteditable', true);
//
//
//    $(this).focus().off('keydown').on('keydown', function(event){
//        if(event.keyCode === 37 || event.keyCode === 39){ // Left/Right
//            var caretPosition = getCaretPosition(this);
//
//            if(caretPosition === this.innerHTML.length || caretPosition === 0){
//                editableRow(this);
//                var $this = $(this),
//                    $table = $this.parents('table'),
//                    $td = $table.find('td'),
//                    index = $td.index($this);
//
//                if(event.keyCode === 37 && caretPosition === 0){
//                    $td.eq(index-1).trigger('click');
//                }else if(event.keyCode === 39 && caretPosition === this.innerHTML.length){
//                    $td.eq(index+1).trigger('click');
//                }
//            }
//        } else if(event.keyCode === 40 || event.keyCode === 38){ // Up/Down
//            event.preventDefault();
//            editableRow(this);
//            var $this = $(this),
//                $parent = $this.parents('table'),
//                $parentTR = $this.parent(),
//                $tr = $parent.find('tr:not(.not)'),
//                index = $parentTR.children().index($this);
//
//            if(event.keyCode === 38){
//                $tr.eq($tr.index($parentTR) - 1).children().eq(index).trigger('click');
//            }else{
//
//                if($tr.index($parentTR) +5 >= $tr.length){
//                    $('.js-getNextPage').trigger('mouseup');
//                    $tr = $parent.find('tr:not(.not)');
//                }
//                $tr.eq($tr.index($parentTR) + 1).children().eq(index).trigger('click');
//            }
//        }else if(event.keyCode === 9){ // Tab
//            event.preventDefault();
//            editableRow(this);
//            var $this = $(this),
//                $table = $this.parents('table'),
//                $td = $table.find('td'),
//                index = $td.index($this);
//
//            $td.eq(index+1).trigger('click');
//        }else if(event.keyCode === 13){ // Enter
//            event.preventDefault();
//            $(document).trigger('mousedown');
//        }
//    });
//
//}).on('keyup', function(event){
//    if(event.keyCode === 27){ // ESC
//        $('.js-structTable .editable').each(function (){
//            this.className = '';
//            this.setAttribute('contenteditable', false);
//        });
//    }
//});
//
//
//function editableRow(t){
//
//    var defValue = t.defaultValue,
//        value = t.innerHTML;
//
//
//    if(value !== defValue){
//        var $row = $(t).parent().children('td'),
//            action = {
//                action: 'editRowInTable'
//            },
//            loadSpin = setTimeout(function(){
//                spin();
//            }, 300);
//
//
//
//        action.table = $('.js-structTables .list-active_item').data('table');
//        action.row = [];
//
//        $row.each(function(){
//            action.row.push({
//                name: this.getAttribute('data-name'),
//                defValue: this.defaultValue ? this.defaultValue : this.innerHTML,
//                newValue: this.innerHTML
//            });
//            this.defaultValue = this.innerHTML;
//        });
//
//        $.post('', action, function(res){
//            clearTimeout(loadSpin);
//            stopSpin();
//            Notification.set({
//                message: res,
//                flag: 'good'
//            });
//            Notification.show();
//        });
//    }
//}