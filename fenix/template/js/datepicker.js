var Datepicker = function(){

    var NAMESPACE = 'datepicker',
        CLASS_DATEPICKER = 'datepicker',
        CLASS_DATEPICKER_HEADER = 'datepicker-header',
        CLASS_DATEPICKER_BLOCK = 'datepicker-block',
        CLASS_DATEPICKER_LEFT_BUTTON = 'datepicker-left',
        CLASS_DATEPICKER_RIGHT_BUTTON = 'datepicker-right',
        CLASS_DATEPICKER_TITLE = 'datepicker-title',
        SELF_DAY = new Date,
        PERIOD = [],
        PARENT = false,
        CURRENT = new Date(SELF_DAY.getFullYear(), SELF_DAY.getMonth(), SELF_DAY.getDate()),
        CURRENT_DATE = new Date(SELF_DAY.getFullYear(), SELF_DAY.getMonth(), SELF_DAY.getDate()),
        lib = {
            month_names: ['Январь', 'Февраль', 'Март', 'Апрель', 'Май', 'Июнь', 'Июль', 'Август', 'Сентябрь', 'Октябрь', 'Ноябрь', 'Декабрь'],
            month_namesGenitive: ['Января', 'Февраля', 'Марта', 'Апреля', 'Мая', 'Июня', 'Июля', 'Августа', 'Сентября', 'Октября', 'Ноября', 'Декабря'],
            abbreviation: ['Янв', 'Февр', 'Март', 'Апр', 'Май', 'Июнь', 'Июль', 'Авг', 'Сент',  'Окт', 'Нояб', 'Дек'],
            formatAlias: [
                'd', // от 01 до 31
                'j', // от 1 до 31
                'F', // от January до December
                'm', // от 01 до 12
                'M', // от Jan до Dec
                'n', // от 1 до 12
                'Y', // Примеры: 1999, 2003
                'y'  // Примеры: 99, 03
            ],
            formatInput: 'j.n.Y',
            formatOutput: 'd.m.Y'
        };

    lib.dateToString = function(format, date){
        var i = 0, len = format.length, result = [],
            day = date.getDate(),
            month = date.getMonth() + 1,
            year = date.getFullYear();

        for(; i < len; i++){
            if(inArray(lib.formatAlias, format[i])){
                switch (format[i]){
                    case 'd' : result.push((day < 10) ? '0' + day : day); break;
                    case 'j': result.push(day); break;
                    case 'F': result.push(lib.month_namesGenitive[month -1]); break;
                    case 'm': result.push((month < 10) ? '0' + month : month); break;
                    case 'M': result.push(lib.abbreviation[month -1]); break;
                    case 'n': result.push(month); break;
                    case 'Y': result.push(year); break;
                    case 'y': result.push(('' + year).substring(2)); break;
                }
            }else{
                result.push(format[i]);
            }
        }
        return result.join('');
    };

    lib.stringToDate = function(format, string){
        var i = 0, len = string.length,
            num = '',
            date = [];
        for(; i < len; i++){
            if(!isNaN(string[i])){
                num += string[i];
            }else{
                date.push(num);
                num = '';
            }
        }
        if(num !== ''){
            date.push(num);
        }

        i = 0; len = format.length;
        var result = {}, count = 0;
        for(; i < len; i++){
            if(inArray(lib.formatAlias, format[i])){
                switch (format[i]){
                    case 'd':
                    case 'j':
                        if(!date[count]) return false;
                        result.day = date[count];
                        count++;
                    break;
                    case 'F':
                    case 'M':
                    case 'm':
                    case 'n':
                        if(!date[count]) return false;
                        result.month = date[count];
                        count++;
                    break;
                    case 'y':
                    case 'Y':
                        if(!date[count]) return false;
                        result.year = date[count];
                        count++;
                    break;
                }
            }
        }

        return new Date(result.year, result.month -1, result.day);
    };

    lib.getDays = function (year, month) {
        var i, days = [],
            date = new Date(year, month),
            backDate = new Date(date.getFullYear(), date.getMonth() - 1);
        lib.current = new Date(year, month);
        for (i = 0; i < lib.getDay(date); i += 1) {
            days.push({
                day: lib.daysInMonth(backDate) - (lib.getDay(date) - i - 1),
                month: backDate.getMonth(),
                year: backDate.getFullYear()
            });
        }
        while (date.getMonth() === month) {
            days.push({
                day: date.getDate(),
                month: date.getMonth(),
                year: date.getFullYear()
            });
            date.setDate(date.getDate() + 1);
        }
        if (lib.getDay(date) !== 0) {
            for (i = lib.getDay(date); i < 7; i += 1) {
                days.push({
                    day: date.getDate(),
                    month: date.getMonth(),
                    year: date.getFullYear()
                });
                date.setDate(date.getDate() + 1);
            }
        }
        return {
            year: year,
            month: month,
            days: days
        };
    };
    lib.getDay = function (date) {
        var day = date.getDay();
        if (day === 0) { day = 7; }
        return day - 1;
    };
    lib.daysInMonth = function (date) {
        return 33 - new Date(date.getFullYear(), date.getMonth(), 33).getDate();
    };
    lib.createCalendar = function (year, month) {
        var i, item, days = lib.getDays(year, month),
            eLeftButton = document.createElement('SPAN'),
            eRightButton = document.createElement('SPAN'),
            eTitle = document.createElement('SPAN'),
            eBox = document.createElement('DIV'),
            eHeader = document.createElement('DIV'),
            eBlock = document.createElement('DIV');

        eBox.className = CLASS_DATEPICKER;
        eHeader.className = CLASS_DATEPICKER_HEADER;
        eBlock.className = CLASS_DATEPICKER_BLOCK;

        eBox.appendChild(eHeader);
        eBox.appendChild(eBlock);

        /* Header */
        eTitle.innerHTML = lib.month_names[days.month] + ' ' + days.year;
        eTitle.className = CLASS_DATEPICKER_TITLE;
        eLeftButton.className = CLASS_DATEPICKER_LEFT_BUTTON;
        eRightButton.className = CLASS_DATEPICKER_RIGHT_BUTTON;
        eHeader.appendChild(eLeftButton);
        eHeader.appendChild(eTitle);
        eHeader.appendChild(eRightButton);

        /* Block */
        for (i = 0; i < days.days.length; i += 1) {
            item = document.createElement('DIV');
            item.innerHTML = days.days[i].day;

            item.setAttribute('year', days.days[i].year);
            item.setAttribute('month', days.days[i].month);
            item.setAttribute('day', days.days[i].day);

            if(!lib.isDisabled(days.days[i], item)){
                lib.isCurrent(days.days[i], item);
            }

            eBlock.appendChild(item);
        }
        eBox[NAMESPACE] = lib
        return eBox;
    };


    lib.isDisabled = function(item, node){
        var isDisable = false, itemPeriod = {}, i = 0, len = PERIOD.length, itemStamp = (new Date(item.year, item.month, item.day)).getTime();
        for(; i < len; i++){
            var period = PERIOD[i];

            if(period.form && period.to){
                if(itemStamp >= period.from.getTime() && itemStamp <= period.to.getTime()){
                    itemPeriod = period;
                    isDisable = true;
                    break;
                }
            }else if(!period.form){ // to
                if(itemStamp <= period.to.getTime()){
                    itemPeriod = period;
                    isDisable = true;
                    break;
                }
            }else{ // form
                if(itemStamp >= period.to.getTime()){
                    itemPeriod = period;
                    isDisable = true;
                    break;
                }
            }
        }

        if(isDisable && node){
            node.className += ' '+NAMESPACE+'-disable';
            if(itemPeriod.help){
                node.setAttribute('help', itemPeriod.help);
            }
        }

        return isDisable;
    };
    lib.isCurrent = function(item, node){
        var day = CURRENT_DATE.getDate(),
            month = CURRENT_DATE.getMonth(),
            year = CURRENT_DATE.getFullYear(),
            isCurrent = false;

        isCurrent = (item.day === day && item.month === month && item.year === year);

        if(node && isCurrent){
            node.className += NAMESPACE + '_current_day';
        }

        return isCurrent;
    };

    lib.appendDropdown = function(node){
        $(node).parents('.dropdown:first').children('.dropdown-block').html(lib.createCalendar(CURRENT.getFullYear(), CURRENT.getMonth())).append('<i class="dropdown-tail"/>');
    };

    lib.change = function(node){

        if(node.hasAttribute(NAMESPACE+'-format')){
            lib.formatOutput = node.getAttribute(NAMESPACE+'-format');
        }

        if(node.tagName === 'INPUT' || node.tagName === 'TEXTAREA'){
            node.value = lib.dateToString(lib.formatOutput, CURRENT_DATE);
        }else{
            node.innerHTML = lib.dateToString(lib.formatOutput, CURRENT_DATE);
        }

        lib.appendDropdown(node);

        if(node.getAttribute(NAMESPACE) !== ''){
            GLOBAL.set(node.getAttribute(NAMESPACE), node);
        }
    };

    lib.init = function($elements, isParent){
        var node;
        if($elements && isParent){
            node = $elements.find('['+NAMESPACE+']');
        } else if ($elements) {
            node = $elements;
        }else{
            node = $('['+NAMESPACE+']');
        }
        node.each(function(){
            if(!this.hasAttribute(NAMESPACE)) return;

            if(this.getAttribute(NAMESPACE) !== ''){
                GLOBAL.set(this.getAttribute(NAMESPACE), this);
            }


            if(this.getAttribute(NAMESPACE + '-parent')){ // Вотчим родительский датапикер
                GLOBAL.watch(this.getAttribute(NAMESPACE + '-parent'), function(node){
                    PARENT = node;
                });
            }

            if(this.getAttribute(NAMESPACE + '-period')){ // Вотчим родительский датапикер
                GLOBAL.watch(this.getAttribute(NAMESPACE + '-period'), function(periods){
                    if(!is(periods, 'Array')) return;
                    PERIOD = [];
                    var i = 0, len = periods.length;
                    for(; i < len; i++){
                        if(!is(periods[i], 'Object')) continue;
                        if(!periods[i].from && !periods[i].to) continue;

                        var newPeriod = {};
                        if(periods[i].from) newPeriod.from = lib.stringToDate(lib.formatInput, periods[i].from);
                        if(periods[i].to) newPeriod.to = lib.stringToDate(lib.formatInput, periods[i].to);
                        if(periods[i].help) newPeriod.help = periods[i].help;

                        PERIOD.push(newPeriod);
                    }
                });
            }


            var self = this,
                dropdownBlock = $('<span class="dropdown-block"/>');
            dropdownBlock.append($('<i class="dropdown-tail"/>'));
            $(this).wrap('<span class="dropdown" />');
            $(this).parent().append(dropdownBlock);
            $(this).wrap('<span class="dropdown-name"/>');

            lib.change(this);

            dropdownBlock.on('mouseup.'+NAMESPACE, '.'+CLASS_DATEPICKER_LEFT_BUTTON+', .'+CLASS_DATEPICKER_RIGHT_BUTTON, function(event){
                event.stopPropagation();
                var year = CURRENT.getFullYear(),
                    month = CURRENT.getMonth();
                if(this.className.indexOf(CLASS_DATEPICKER_LEFT_BUTTON) > -1){
                    //left
                    if(month - 1 < 0){
                        year -= 1;
                        month = 11;
                    }else{
                        month -= 1;
                    }
                }else{
                    if(month + 1 > 11){
                        year += 1;
                        month = 0;
                    }else{
                        month += 1;
                    }
                }
                CURRENT = new Date(year, month);
                lib.appendDropdown(self);
            }).on('mouseup.'+NAMESPACE, '.'+CLASS_DATEPICKER_BLOCK+' div:not(.'+NAMESPACE+'-disable'+')', function(event){
                event.stopPropagation();
                year = this.getAttribute('year') * 1,
                month = this.getAttribute('month') * 1,
                day = this.getAttribute('day') * 1;

                CURRENT_DATE = new Date(year, month, day);
                lib.change(self);


                $(document).trigger('dropdown-close');
            });
        });
    };
    return lib;
}();

$(function(){
    Datepicker.init();
});

