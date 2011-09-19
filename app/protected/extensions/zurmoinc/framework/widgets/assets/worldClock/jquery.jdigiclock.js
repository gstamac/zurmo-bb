/*
 * jDigiClock plugin 2.1
 *
 * http://www.radoslavdimov.com/jquery-plugins/jquery-plugin-digiclock/
 *
 * Copyright (c) 2009 Radoslav Dimov
 *
 * Dual licensed under the MIT and GPL licenses:
 * http://www.opensource.org/licenses/mit-license.php
 * http://www.gnu.org/licenses/gpl.html
 *
 */


(function($) {
    $.fn.extend({

        jdigiclock: function(options) {

            var defaults = {
                uniqueLayoutId: null,
                imagesPath: 'images/',
                clockImagesPath: 'images/clock/',
                weatherImagesPath: 'images/weather/',
                lang: 'en',
                am_pm: false,
                weatherLocationCode: 'EUR|BG|BU002|BOURGAS',
                weatherMetric: 'C',
                weatherUpdate: 0,
                weatherUrl: null
            };

            var regional = [];
            regional['en'] = {
                monthNames: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
                dayNames: ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat']
            }


            var options = $.extend(defaults, options);

            return this.each(function() {

                var $this = $(this);
                var o = options;
                $this.uniqueLayoutId    = o.uniqueLayoutId;
                $this.weatherUrl        = o.weatherUrl;
                $this.clockImagesPath   = o.clockImagesPath;
                $this.weatherImagesPath = o.weatherImagesPath;
                $this.imagesPath        = o.imagesPath;
                $this.lang = regional[o.lang] == undefined ? regional['en'] : regional[o.lang];
                $this.am_pm = o.am_pm;
                $this.weatherLocationCode = o.weatherLocationCode;
                $this.weatherMetric = o.weatherMetric == 'C' ? 1 : 0;
                $this.weatherUpdate = o.weatherUpdate;
                $this.proxyType = o.proxyType;
                $this.currDate = '';
                $this.timeUpdate = '';

                var html = '<div id="worldclock-container-' + $this.uniqueLayoutId + '" class="worldclock-container">';
                html    += '<p id="worldclock-left-arrow-' + $this.uniqueLayoutId + '" class="worldclock-left-arrow"><img src="' + $this.imagesPath + 'icon_left.png" /></p>';
                html    += '<p id="worldclock-right-arrow-' + $this.uniqueLayoutId + '" class="worldclock-right-arrow"><img src="' + $this.imagesPath + 'icon_right.png" /></p>';
                html    += '<div class="worldclock-digital-container">';
                html    += '<div id="worldclock-clock-' + $this.uniqueLayoutId + '" class="worldclock-clock"></div>';
                html    += '<div id="worldclock-weather-' + $this.uniqueLayoutId + '" class="worldclock-weather"></div>';
                html    += '</div>';
                html    += '<div id="worldclock-forecast-container-' + $this.uniqueLayoutId + '" class="worldclock-forecast-container"></div>';
                html    += '</div>';

                $this.html(html);

                $this.displayClock($this);

                $this.displayWeather($this);

                var panel_pos = ($('#worldclock-container-' + $this.uniqueLayoutId + ' > div').length - 1) * 500;
                var next = function() {
                    $('#worldclock-right-arrow-' + $this.uniqueLayoutId + '').unbind('click', next);
                    $('#worldclock-container-' + $this.uniqueLayoutId + ' > div').filter(function(i) {
                        $(this).animate({'left': (i * 500) - 500 + 'px'}, 400, function() {
                            if (i == 0) {
                                $(this).appendTo('#worldclock-container-' + $this.uniqueLayoutId + '').css({'left':panel_pos + 'px'});
                            }
                            $('#worldclock-right-arrow-' + $this.uniqueLayoutId + '').unbind('click', next);
                            $('#worldclock-right-arrow-' + $this.uniqueLayoutId + '').bind('click', next);
                        });
                    });
                };
                $('#worldclock-right-arrow-' + $this.uniqueLayoutId + '').bind('click', next);

                var prev = function() {
                    $('#worldclock-left-arrow-' + $this.uniqueLayoutId + '').unbind('click', prev);
                    $('#worldclock-container-' + $this.uniqueLayoutId + ' > div:last').prependTo('#worldclock-container-' + $this.uniqueLayoutId + '').css({'left':'-500px'});
                    $('#worldclock-container-' + $this.uniqueLayoutId + ' > div').filter(function(i) {
                        $(this).animate({'left': i * 500 + 'px'}, 400, function() {
                            $('#worldclock-left-arrow-' + $this.uniqueLayoutId + '').unbind('click', prev);
                            $('#worldclock-left-arrow-' + $this.uniqueLayoutId + '').bind('click', prev);
                        });
                    });
                };
                $('#worldclock-left-arrow-' + $this.uniqueLayoutId + '').bind('click', prev);

            });
        }
    });


    $.fn.displayClock = function(el) {
        $.fn.getTime(el);
        setTimeout(function() {$.fn.displayClock(el)}, $.fn.delay());
    }

    $.fn.displayWeather = function(el) {
        $.fn.getWeather(el);
        if (el.weatherUpdate > 0) {
            setTimeout(function() {$.fn.displayWeather(el)}, (el.weatherUpdate * 60 * 1000));
        }
    }

    $.fn.delay = function() {
        var now = new Date();
        var delay = (60 - now.getSeconds()) * 1000;

        return delay;
    }

    $.fn.getTime = function(el) {
        var now = new Date();
        var old = new Date();
        old.setTime(now.getTime() - 60000);

        var now_hours, now_minutes, old_hours, old_minutes, timeOld = '';
        now_hours =  now.getHours();
        now_minutes = now.getMinutes();
        old_hours =  old.getHours();
        old_minutes = old.getMinutes();

        if (el.am_pm) {
            var am_pm = now_hours > 11 ? 'pm' : 'am';
            now_hours = ((now_hours > 12) ? now_hours - 12 : now_hours);
            old_hours = ((old_hours > 12) ? old_hours - 12 : old_hours);
        }

        now_hours   = ((now_hours <  10) ? "0" : "") + now_hours;
        now_minutes = ((now_minutes <  10) ? "0" : "") + now_minutes;
        old_hours   = ((old_hours <  10) ? "0" : "") + old_hours;
        old_minutes = ((old_minutes <  10) ? "0" : "") + old_minutes;
        // date
        el.currDate = el.lang.dayNames[now.getDay()] + ',&nbsp;' + now.getDate() + '&nbsp;' + el.lang.monthNames[now.getMonth()];
        // time update
        el.timeUpdate = el.currDate + ',&nbsp;' + now_hours + ':' + now_minutes;

        var firstHourDigit = old_hours.substr(0,1);
        var secondHourDigit = old_hours.substr(1,1);
        var firstMinuteDigit = old_minutes.substr(0,1);
        var secondMinuteDigit = old_minutes.substr(1,1);

        timeOld += '<div id="worldclock-hours-' + el.uniqueLayoutId + '" class="worldclock-hours"><div class="line"></div>';
        timeOld += '<div id="worldclock-hours-bg-' + el.uniqueLayoutId + '" class="worldclock-hours-bg"><img src="' + el.clockImagesPath + 'clockbg1.png" /></div>';
        timeOld += '<img src="' + el.clockImagesPath + firstHourDigit + '.png" id="fhd-' + el.uniqueLayoutId + '" class="first_digit" />';
        timeOld += '<img src="' + el.clockImagesPath + secondHourDigit + '.png" id="shd-' + el.uniqueLayoutId + '" class="second_digit" />';
        timeOld += '</div>';
        timeOld += '<div id="worldclock-minutes-' + el.uniqueLayoutId + '" class="worldclock-minutes"><div class="line"></div>';
        if (el.am_pm) {
            timeOld += '<div class="worldclock-am-pm"><img src="' + el.clockImagesPath + am_pm + '.png" /></div>';
        }
        timeOld += '<div id="worldclock-minutes-bg-' + el.uniqueLayoutId + '" class="worldclock-minutes-bg"><img src="' + el.clockImagesPath + 'clockbg1.png" /></div>';
        timeOld += '<img src="' + el.clockImagesPath + firstMinuteDigit + '.png" id="fmd-' + el.uniqueLayoutId + '" class="first_digit" />';
        timeOld += '<img src="' + el.clockImagesPath + secondMinuteDigit + '.png" id="smd-' + el.uniqueLayoutId + '" class="second_digit" />';
        timeOld += '</div>';

        el.find('#worldclock-clock-' + el.uniqueLayoutId).html(timeOld);

        // set minutes
        if (secondMinuteDigit != '9') {
            firstMinuteDigit = firstMinuteDigit + '1';
        }

        if (old_minutes == '59') {
            firstMinuteDigit = '511';
        }

        setTimeout(function() {
            $('#fmd-' + el.uniqueLayoutId).attr('src', el.clockImagesPath + firstMinuteDigit + '-1.png');
            $('#worldclock-minutes-bg-' + el.uniqueLayoutId + ' img').attr('src', el.clockImagesPath + 'clockbg2.png');
        },200);
        setTimeout(function() { $('#worldclock-minutes-bg-' + el.uniqueLayoutId + ' img').attr('src', el.clockImagesPath + 'clockbg3.png')},250);
        setTimeout(function() {
            $('#fmd-' + el.uniqueLayoutId).attr('src', el.clockImagesPath + firstMinuteDigit + '-2.png');
            $('#worldclock-minutes-bg-' + el.uniqueLayoutId + ' img').attr('src', el.clockImagesPath + 'clockbg4.png');
        },400);
        setTimeout(function() { $('#worldclock-minutes-bg-' + el.uniqueLayoutId + ' img').attr('src', el.clockImagesPath + 'clockbg5.png')},450);
        setTimeout(function() {
            $('#fmd-' + el.uniqueLayoutId).attr('src', el.clockImagesPath + firstMinuteDigit + '-3.png');
            $('#worldclock-minutes-bg-' + el.uniqueLayoutId + ' img').attr('src', el.clockImagesPath + 'clockbg6.png');
        },600);

        setTimeout(function() {
            $('#smd-' + el.uniqueLayoutId).attr('src', el.clockImagesPath + secondMinuteDigit + '-1.png');
            $('#worldclock-minutes-bg-' + el.uniqueLayoutId + ' img').attr('src', el.clockImagesPath + 'clockbg2.png');
        },200);
        setTimeout(function() { $('#worldclock-minutes-bg-' + el.uniqueLayoutId + ' img').attr('src', el.clockImagesPath + 'clockbg3.png')},250);
        setTimeout(function() {
            $('#smd-' + el.uniqueLayoutId).attr('src', el.clockImagesPath + secondMinuteDigit + '-2.png');
            $('#worldclock-minutes-bg-' + el.uniqueLayoutId + ' img').attr('src', el.clockImagesPath + 'clockbg4.png');
        },400);
        setTimeout(function() { $('#worldclock-minutes-bg-' + el.uniqueLayoutId + ' img').attr('src', el.clockImagesPath + 'clockbg5.png')},450);
        setTimeout(function() {
            $('#smd-' + el.uniqueLayoutId).attr('src', el.clockImagesPath + secondMinuteDigit + '-3.png');
            $('#worldclock-minutes-bg-' + el.uniqueLayoutId + ' img').attr('src', el.clockImagesPath + 'clockbg6.png');
        },600);

        setTimeout(function() {$('#fmd-' + el.uniqueLayoutId).attr('src', el.clockImagesPath + now_minutes.substr(0,1) + '.png')},800);
        setTimeout(function() {$('#smd-' + el.uniqueLayoutId).attr('src', el.clockImagesPath + now_minutes.substr(1,1) + '.png')},800);
        setTimeout(function() { $('#worldclock-minutes-bg-' + el.uniqueLayoutId + ' img').attr('src', el.clockImagesPath + 'clockbg1.png')},850);

        // set hours
        if (now_minutes == '00') {

            if (el.am_pm) {
                if (now_hours == '00') {
                    firstHourDigit = firstHourDigit + '1';
                    now_hours = '12';
                } else if (now_hours == '01') {
                    firstHourDigit = '001';
                    secondHourDigit = '111';
                } else {
                    firstHourDigit = firstHourDigit + '1';
                }
            } else {
                if (now_hours != '10') {
                    firstHourDigit = firstHourDigit + '1';
                }

                if (now_hours == '20') {
                    firstHourDigit = '1';
                }

                if (now_hours == '00') {
                    firstHourDigit = firstHourDigit + '1';
                    secondHourDigit = secondHourDigit + '11';
                }
            }

            setTimeout(function() {
                $('#fhd-' + el.uniqueLayoutId).attr('src', el.clockImagesPath + firstHourDigit + '-1.png');
                $('#worldclock-hours-bg-' + el.uniqueLayoutId + ' img').attr('src', el.clockImagesPath + 'clockbg2.png');
            },200);
            setTimeout(function() { $('#hours-bg-' + el.uniqueLayoutId + ' img').attr('src', el.clockImagesPath + 'clockbg3.png')},250);
            setTimeout(function() {
                $('#fhd-' + el.uniqueLayoutId).attr('src', el.clockImagesPath + firstHourDigit + '-2.png');
                $('#worldclock-hours-bg-' + el.uniqueLayoutId + ' img').attr('src', el.clockImagesPath + 'clockbg4.png');
            },400);
            setTimeout(function() { $('#hours-bg-' + el.uniqueLayoutId + ' img').attr('src', el.clockImagesPath + 'clockbg5.png')},450);
            setTimeout(function() {
                $('#fhd-' + el.uniqueLayoutId).attr('src', el.clockImagesPath + firstHourDigit + '-3.png');
                $('#worldclock-hours-bg-' + el.uniqueLayoutId + ' img').attr('src', el.clockImagesPath + 'clockbg6.png');
            },600);

            setTimeout(function() {
            $('#shd-' + el.uniqueLayoutId).attr('src', el.clockImagesPath + secondHourDigit + '-1.png');
            $('#worldclock-hours-bg-' + el.uniqueLayoutId + ' img').attr('src', el.clockImagesPath + 'clockbg2.png');
            },200);
            setTimeout(function() { $('#worldclock-hours-bg-' + el.uniqueLayoutId + ' img').attr('src', el.clockImagesPath + 'clockbg3.png')},250);
            setTimeout(function() {
                $('#shd-' + el.uniqueLayoutId).attr('src', el.clockImagesPath + secondHourDigit + '-2.png');
                $('#worldclock-hours-bg-' + el.uniqueLayoutId + ' img').attr('src', el.clockImagesPath + 'clockbg4.png');
            },400);
            setTimeout(function() { $('#worldclock-hours-bg-' + el.uniqueLayoutId + ' img').attr('src', el.clockImagesPath + 'clockbg5.png')},450);
            setTimeout(function() {
                $('#shd-' + el.uniqueLayoutId).attr('src', el.clockImagesPath + secondHourDigit + '-3.png');
                $('#worldclock-hours-bg-' + el.uniqueLayoutId + ' img').attr('src', el.clockImagesPath + 'clockbg6.png');
            },600);

            setTimeout(function() {$('#fhd-' + el.uniqueLayoutId).attr('src', el.clockImagesPath + now_hours.substr(0,1) + '.png')},800);
            setTimeout(function() {$('#shd-' + el.uniqueLayoutId).attr('src', el.clockImagesPath + now_hours.substr(1,1) + '.png')},800);
            setTimeout(function() { $('#worldclock-hours-bg-' + el.uniqueLayoutId + ' img').attr('src', el.clockImagesPath + 'clockbg1.png')},850);
        }
    }

    $.fn.getWeather = function(el) {

        el.find('#worldclock-weather-' + el.uniqueLayoutId).html('<p class="loading">Update Weather ...</p>');
        el.find('#worldclock-forecast-container-' + el.uniqueLayoutId).html('<p class="loading">Update Weather ...</p>');
        var metric = el.weatherMetric == 1 ? 'C' : 'F';
        $.getJSON(el.weatherUrl + '&location=' + el.weatherLocationCode + '&metric=' + el.weatherMetric, function(data) {

            el.find('#worldclock-weather-' + el.uniqueLayoutId + ' .loading, #worldclock-forecast-container-' + el.uniqueLayoutId + ' .loading').hide();

            var curr_temp = '<p class="worldclock-temp">' + data.curr_temp + '&deg;<span class="worldclock-metric">' + metric + '</span></p>';

            el.find('#worldclock-weather-' + el.uniqueLayoutId).css('background','url(' + el.weatherImagesPath + data.curr_icon + '.png) 50% 100% no-repeat');
            var weather = '<div class="worldclock-local"><p class="city">' + data.city + '</p><p>' + data.curr_text + '</p></div>';
            weather += '<div class="worldclock-temp"><p class="worldclock-date">' + el.currDate + '</p>' + curr_temp + '</div>';
            el.find('#worldclock-weather-' + el.uniqueLayoutId).html(weather);

            // forecast
            el.find('#worldclock-forecast-container-' + el.uniqueLayoutId).append('<div id="worldclock-current-' + el.uniqueLayoutId + '" class="worldclock-current"></div>');
            var curr_for = curr_temp + '<p class="high_low">' + data.forecast[0].day_htemp + '&deg;&nbsp;/&nbsp;' + data.forecast[0].day_ltemp + '&deg;</p>';
            curr_for    += '<p class="city">' + data.city + '</p>';
            curr_for    += '<p class="text">' + data.forecast[0].day_text + '</p>';
            el.find('#worldclock-current-' + el.uniqueLayoutId).css('background','url(' + el.weatherImagesPath + data.forecast[0].day_icon + '.png) 50% 0 no-repeat').append(curr_for);

            el.find('#worldclock-forecast-container-' + el.uniqueLayoutId).append('<ul id="worldclock-forecast-' + el.uniqueLayoutId + '" class="worldclock-forecast"></ul>');
            data.forecast.shift();
            for (var i in data.forecast) {
                var d_date = new Date(data.forecast[i].day_date);
                var day_name = el.lang.dayNames[d_date.getDay()];
                var forecast = '<li>';
                forecast    += '<p>' + day_name + '</p>';
                forecast    += '<img src="' + el.weatherImagesPath + data.forecast[i].day_icon + '.png" alt="' + data.forecast[i].day_text + '" title="' + data.forecast[i].day_text + '" />';
                forecast    += '<p>' + data.forecast[i].day_htemp + '&deg;&nbsp;/&nbsp;' + data.forecast[i].day_ltemp + '&deg;</p>';
                forecast    += '</li>';
                el.find('#worldclock-forecast-' + el.uniqueLayoutId).append(forecast);
            }

            el.find('#worldclock-forecast-container-' + el.uniqueLayoutId).append('<div class="worldclock-update"><img src="' + el.imagesPath + 'refresh_01.png" alt="reload" title="reload" id="reload" />' + el.timeUpdate + '</div>');

            $('#reload').click(function() {
                el.find('#worldclock-weather-' + el.uniqueLayoutId).html('');
                el.find('#worldclock-forecast-container-' + el.uniqueLayoutId).html('');
                $.fn.getWeather(el);
            });
        });
    }

})(jQuery);