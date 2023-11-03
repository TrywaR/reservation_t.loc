$(function () {
    // $.fn.date_views = function( oBlockData ) {
    //     if ( typeof oBlockData === 'undefined' ) oBlockData = {}

    //     var oReservationT = oReservetionT( this, oBlockData )
    // }
    function oReservetionT() {
        this.arrDisabledDays = []

        // Вывод текущей даты
        this.get_current_date = function () {
            // Получение текущей даты
            var currentDate = new Date();

            // Получение дня, месяца и года
            var year = currentDate.getFullYear();
            var month = (currentDate.getMonth() + 1).toString().padStart(2, '0'); // Месяцы в JavaScript начинаются с 0
            var day = currentDate.getDate().toString().padStart(2, '0');

            // Форматирование даты в строку "YYYY-MM-DD"
            var formattedDate = `${year}-${month}-${day}`;

            return formattedDate
        }

        this.get_convert_date = function(originalDate=''){
            // Исходная дата в формате "16/11/2023"
            // var originalDate = "16/11/2023";

            // Разделение исходной даты на составляющие
            var parts = originalDate.split('/')

            // Создание новой даты в формате "n-j-Y"
            var formattedDate = parts[2] + "-" + parts[1] + "-" + parts[0]

            // Вывод отформатированной даты
            return formattedDate
        }

        // Вывод данных.
        this.show = function (oData) {
            var oReservationTThis = this

            $.ajax({
                type: 'POST',
                url: '/reservation_t/',
                data: oData,
                dataType: 'json',
            }).done(function (oData) {
                $(document).find('#reservation_t_show_result').html('')

                if (typeof oData.data != 'undefined') {
                    $.each(oData.data, function (iIndex, oElem) {
                        var sResult = ''
                        if (iIndex == 'disabled_day') oReservationTThis.days_disabled(oElem)
                        if (!oElem.hour) return false


                        sResult += '<li class="list-group-item">'
                        sResult += '<div class="form-group form-check pl-4">'
                        sResult += '<input '
                        if (oElem.check) sResult += 'checked="checked"'
                        if (oElem.id) sResult += ' data-id="' + oElem.id + '"'
                        sResult += 'type="checkbox" class="form-check-input _hour_" name="hour" value="' + oElem.hour + '" id="checkbox_' + oElem.hour + '">'
                        sResult += '<label class="form-check-label" for="checkbox_' + oElem.hour + '">' + oElem.hour + '</label>'
                        sResult += '</div>'
                        sResult += '</li>'

                        $(document).find('#reservation_t_show_result').append(sResult)
                    })
                }
                else {
                    $(document).find('#reservation_t_show_result').html(oData)
                }
            })
            return false
        }

        // Добавление данных
        this.add = function (oData) {
            $(document).find('#reservation_t_show_form').trigger('submit')

            $.ajax({
                type: 'POST',
                url: '/reservation_t/',
                data: oData,
                dataType: 'json',
            }).done(function (oData) {
                $('#reservation_t_add_modal').modal('hide')
            }).fail(function (jqXHR, textStatus) {
                var sTextError = 'Error'
                if ( typeof jqXHR.responseJSON.error != 'undefined' )
                sTextError = jqXHR.responseJSON.error
                alert(sTextError)
                // if (jqXHR.status == 200) alert('Error')
                // else {
                //     if ($(oElem).attr('name') == 'fullday') {
                //         oReservationTThis.days_disabled(0)
                //     }
                // }
            })
        }

        // Редактирование
        this.edit = function (oData, oElem) {
            var oReservationTThis = this

            $.ajax({
                type: 'POST',
                url: '/reservation_t/',
                data: oData,
                dataType: 'json',
            }).done(function (oData) {
                if (typeof oData.data != 'undefined') {
                    if (oData.data.id) {
                        $(oElem).attr('data-id', oData.data.id)
                        if ($(oElem).attr('name') == 'fullday') {
                            oReservationTThis.days_disabled(oData.data.id)
                        }
                    }
                }

            }).fail(function (jqXHR, textStatus,tes) {
                var sTextError = 'Error'
                if ( typeof jqXHR.responseJSON.error != 'undefined' )
                sTextError = jqXHR.responseJSON.error
                alert(sTextError)
                // if (jqXHR.status == 200) alert('Error')
                // else {
                //     if ($(oElem).attr('name') == 'fullday') {
                //         oReservationTThis.days_disabled(0)
                //     }
                // }
            })
        }

        // Бронирование всего дня
        this.days_disabled = function (iId) {
            // День забронирован весь
            if (iId) {
                if ($(document).find('#reservation_t_edit_form').length) {
                    $(document).find('#reservation_t_edit_form [name=fullday]').prop('checked', true)
                    $(document).find('#reservation_t_edit_form [name=fullday]').attr('data-id', iId)
                    $(document).find('#reservation_t_edit_form ._hour_').each(function () {
                        $(this).addClass('disabled')
                        $(this).attr('disabled', true)
                        $(this).parents('li').addClass('disabled')
                    })
                }
            }
            else {
                if ($(document).find('#reservation_t_edit_form').length) {
                    $(document).find('#reservation_t_edit_form [name=fullday]').prop('checked', false)
                    $(document).find('#reservation_t_edit_form [name=fullday]').attr('data-id', 0)
                    $(document).find('#reservation_t_edit_form ._hour_').each(function () {
                        $(this).removeClass('disabled')
                        $(this).attr('disabled', false)
                        $(this).parents('li').removeClass('disabled')
                    })
                }
            }
        }

        // Вывод возможных часов для бронирования
        this.options = function (oData) {
            var oReservationTThis = this

            $.ajax({
                type: 'POST',
                url: '/reservation_t/',
                data: oData,
                dataType: 'json',
            }).done(function (oData) {
                $(document).find('#reservation_t_result').html('')

                if (typeof oData.data != 'undefined') {
                    $.each(oData.data, function (iIndex, oElem) {
                        sResult = '<option value="' + oElem.hour + '">' + oElem.hour + '</option>'
                        $(document).find('#reservation_t_result').append(sResult)
                    })
                }
                else {
                    $(document).find('#reservation_t_result').html(oData)
                }
            })
            return false
        }

        // Вывод данных.
        this.get_disabled_days = function () {
            var oReservationTThis = this

            $.ajax({
                type: 'POST',
                url: '/reservation_t/',
                data: {
                    'reservation_t': true,
                    'event': 'show_disabled_days',
                },
                dataType: 'json',
            }).done(function (oData) {
                oReservationTThis.arrDisabledDays = oData
                oReservationTThis.datepicker()
            })
            return false
        }

        this.datepicker = function () {
            var oReservationTThis = this

            if (!$(document).find('#reservation_t_input').hasClass('datepicker')) {
                $(document).find('#reservation_t_input').on('change', function (event) {
                    var sDate = ''

                    if ($(document).find('#reservation_t_result').length) {
                        var sDate = $(document).find('#reservation_t_input').val()
                    }

                    oReservationTThis.options({
                        'reservation_t': true,
                        'date': oReservationTThis.get_convert_date(sDate),
                        'event': 'options',
                    })
                })

                return false
            }
            // ___
            // Заблоченные даты
            var disabledDays = oReservationTThis.arrDisabledDays

            //исправление бага со временем, последний час брони 22:30
            //так как у пользователя временной отрезок отображается now_time+2часа
            //последнее время для брони в этот день 20:20
            //если пользователь пытается забронировать позже время в заявке отображаться не будет
            function getMinDate(min_date) {
                return min_date.getMonth() + 1 + "-" + min_date.getDate() + "-" + min_date.getFullYear();
            }


            var now_date = new Date();
            var min_date = getMinDate(now_date);
            var timeFalse = true // Добавить 30 минут в начале
            var timeEnd = false // Добавить 30 минут вконце

            if (now_date.getHours() > 20 || (now_date.getUTCHours() + 3 == 20 && now_date.getMinutes() > 20)) {
                disabledDays.push(min_date);
            }

            //такая же проблема и с зарезервированными днями
            if (disabledDays.indexOf(min_date) != -1) {
                while (disabledDays.indexOf(min_date) != -1) {
                    //проверяем свободен ли следующий день
                    now_date.setDate(now_date.getDate() + 1);
                    min_date = getMinDate(now_date);
                }
            }

            function disableAllTheseDays(date) {
                var m = date.getMonth(),
                    d = date.getDate(),
                    y = date.getFullYear();
                for (i = 0; i < disabledDays.length; i++) {
                    if ($.inArray((m + 1) + '-' + d + '-' + y, disabledDays) != -1) {
                        return [false];
                    }
                }
                return [true];
            }

            //Jqeury ui - ввод даты
            $(".datepicker").datepicker({
                dateFormat: 'dd/mm/yy',
                beforeShowDay: disableAllTheseDays,
                minDate: now_date,
                firstDay: 1,
                closeText: 'Закрыть',
                prevText: 'Пред',
                nextText: 'След',
                currentText: 'Сегодня',
                monthNames: ['Январь', 'Февраль', 'Март', 'Апрель', 'Май', 'Июнь', 'Июль', 'Август', 'Сентябрь', 'Октябрь', 'Ноябрь', 'Декабрь'],
                monthNamesShort: ['Янв', 'Фев', 'Мар', 'Апр', 'Май', 'Июн', 'Июл', 'Авг', 'Сен', 'Окт', 'Ноя', 'Дек'],
                dayNames: ['воскресенье', 'понедельник', 'вторник', 'среда', 'четверг', 'пятница', 'суббота'],
                dayNamesShort: ['вск', 'пнд', 'втр', 'срд', 'чтв', 'птн', 'сбт'],
                dayNamesMin: ['Вс', 'Пн', 'Вт', 'Ср', 'Чт', 'Пт', 'Сб'],
                weekHeader: 'Нед',
                onSelect: function (dateText, inst) {
                    // Смена даты и показ свободного времени
                    oReservationTThis.options({
                        'reservation_t': true,
                        'date': oReservationTThis.get_convert_date(dateText),
                        'event': 'options',
                    })
                  }
            })
            $(".datepicker").datepicker('setDate', new Date())

            // Первый показ возможного времени
            var sDate = $(document).find('#reservation_t_input').val()

            oReservationTThis.options({
                'reservation_t': true,
                'date': oReservationTThis.get_convert_date(sDate),
                'event': 'options',
            })

        }

        this.input_init = function() {
            var oReservationTThis = this

            oReservationTThis.get_disabled_days()
        }

        // Запуск работы
        this.init = function () {
            var oReservationTThis = this

            // КЛИЕНТ:Поле выбора даты для бронирования
            if ($(document).find('#reservation_t_input').length) {
                oReservationTThis.input_init()
            }

            // АДМИНКА: Форма добавления брони
            if ($(document).find('#reservation_t_add_form').length) {
                $(document).find('#reservation_t_add_form').on('submit', function (event) {
                    event.preventDefault()

                    oReservationTThis.add($(this).serializeArray())
                    return false
                })
            }

            // АДМИНКА
            // Смена даты
            if ($(document).find('#reservation_t_edit_form').length) {
                $(document).on('change', '#reservation_t_edit_form input', function (event) {
                    event.preventDefault()

                    var oData = {}

                    oData['date'] = $(this).parents('form').find('[name=date]').val()
                    oData['reservation_t'] = true
                    oData['hour'] = $(this).val()
                    oData['id'] = $(this).attr('data-id')

                    if ($(this).prop('checked')) oData['event'] = 'add'
                    else oData['event'] = 'del'

                    oReservationTThis.edit(oData, this)
                    return false
                })
            }


            if (!$(document).find('#reservation_t_show_form').length) return false

            // Подставляем текущую дату
            $(document).find('#reservation_t_show_form').find('[name=date]').val(this.get_current_date())

            $(document).find('#reservation_t_show_form input').on('change', function () {
                $(document).find('#reservation_t_show_form').trigger('submit')
            })

            $(document).find('#reservation_t_show_form').on('submit', function (event) {
                event.preventDefault()

                if ($(document).find('#reservation_t_edit_form').length) {
                    var sDate = $(this).find('[name=date]').val()

                    $(document).find('#reservation_t_edit_form').find('[name=date]').val(sDate)
                }

                oReservationTThis.show($(this).serializeArray())
                return false
            })

            $(document).find('#reservation_t_show_form').trigger('submit')
        }

        this.init()
    }

    var oReservationT = oReservetionT()
})