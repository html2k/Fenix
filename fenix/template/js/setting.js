$(document).on('click', '.js-user-list', function(){
    var login = this.getAttribute('data-login'),
        access = this.getAttribute('data-access'),
        id = this.getAttribute('data-id'),
        $popup = LIB.popup('userSetting'),
        $login = $popup.find('input[name=name]'),
        $id = $popup.find('input[name=id]'),
        $select = $popup.find('select[name=access]');

    $login.val(login);
    $id.val(id);

    $select.children('option').each(function(k, v){
        if(this.value === access){
            $select.parent().find('.btn-in__first').text(this.innerHTML);
            return;
        }
    });

    $login.trigger('input');
});