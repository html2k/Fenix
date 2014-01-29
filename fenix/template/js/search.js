$(document).on('input', '.header-search input', function(){
    var value = this.value,
        block = $(this).parent().children('.header-search-result-block');

    if(value.length > 3){
        search(value, block);
    }

}).on('keyup', '.header-search input', function(event){
    var value = this.value,
        block = $(this).parent().children('.header-search-result-block');
    if(event.keyCode === 13 && value.length > 0){
        search(value, block);
    }

    if(value.length > 0){
        $(this).addClass('active');
    }else{
        block.slideUp(100);
        $(this).removeClass('active');
    }

}).on('mouseup', function(event){
    if($(event.target).closest('.header-search').length) return;
    $('.header-search-result-block').stop().slideUp(100);
});


function search(value, block){
    $.post('', {
        action : 'search',
        value : value
    }, function(res){
        var list = document.createElement('UL');
        var count = 0;
        for(var i = 0; i < res.length; i++){
            var li = addResultSearch(res[i]);
            list.appendChild(li);
            count += res[i].find.length;
        }
        if(count > 0){
            block.html(list);
            //block.append('<span class="header-search-all">'+Показать все результаты+'</span>');
            block.slideDown(150);
        }else{
            block.html();
            block.slideUp(150);
        }
    });
}

function addResultSearch(res){
    var li = document.createElement('LI'),
        head = document.createElement('H3'),
        list = document.createElement('UL');
    head.innerHTML = res.name;

    li.appendChild(head)
    li.appendChild(list);

    var max = res.find.length > 5 ? 5 : res.find.length;
    console.log(max)
    for(var i = 0; i < max; i++){
        var item = document.createElement('LI'),
            name = res.find[i].name ? res.find[i].name : 'undefiend - ' + res.find[i].id,
            a = document.createElement('A'),
            block = document.createElement('DIV'),
            table = document.createElement('TABLE');

        a.href = '?mode=elem&id='+ res.find[i].id;
        a.appendChild(document.createTextNode(name));
        a.appendChild(block);
        block.appendChild(table);

        table.className = 'table w-big';

        for(var x in res.find[i]){
            var el = res.find[i][x],
                falseBlock = document.createElement('DIV'),
                tr = document.createElement('TR'),
                tdName = document.createElement('TD'),
                tdDesc = document.createElement('TD');

            falseBlock.innerHTML = el;
            el = falseBlock.innerText;

            if(el.length < 1) continue;

            if(el.length > 200){
                el = el.substr(0,200) + '...';
            }


            tdName.appendChild(document.createTextNode(x));
            tdDesc.appendChild(document.createTextNode(el));

            tr.appendChild(tdName);
            tr.appendChild(tdDesc);

            table.appendChild(tr);
        }
        item.appendChild(a);
        list.appendChild(item);

    }
    return li;
}