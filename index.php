<?php
// ПАРАМЕТРЫ

if (true) {
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);

    // Те что ловим
    // $bShowInterfase - Вывод интерфейса
    // $sEvent - Событие для обработки (show,add,del)
    // $arrRequestData - Данные для обработки (date,hour,place,fullday)

    // Те что есть
    $dHourWorkStart = 12; # Час начала рабочего дня
    $dHourWorkStop = 22; # Час конца рабочего дня
    $fHourInterval = 1; # Интервал доступных часов (0 - час, 1 - пол часа)

    // $database_server = 'localhost';
    // $database_user = 'litcafesu_lc';
    // $database_password = 'L]62C@g*';
    // $dbase = 'litcafesu_lc';
    // $sTableName = 'reservation_t'; # Название таблицы
}

// БАЗА
if (true) {

    if (!class_exists('DataBase')) {
        class DataBase
        {
            public $database_server = 'localhost';
            public $database_user = 'litcafesu_lc';
            public $database_password = 'L]62C@g*';
            public $dbase = 'litcafesu_lc';

            function query(String $sQuery = '')
            {
                // Подключаемся
                try {
                    $PDO = new PDO('mysql:host=' . $this->database_server . ';dbname=' . $this->dbase, $this->database_user, $this->database_password);
                } catch (Exception $e) {
                    echo 'Caught exception: ',  $e->getMessage(), "\n";
                    die();
                }

                // - Выполняем запрос
                try {
                    $arrResult = $PDO->query($sQuery)->fetch(PDO::FETCH_ASSOC);
                } catch (Exception $e) {
                    echo 'Caught exception: ',  $e->getMessage(), "\n";
                }

                // - Отключаемся
                $PDO = null;

                return $arrResult;
            }

            function query_all(String $sQuery = '')
            {
                // Подключаемся
                try {
                    $PDO = new PDO('mysql:host=' . $this->database_server . ';dbname=' . $this->dbase, $this->database_user, $this->database_password);
                } catch (Exception $e) {
                    echo 'Caught exception: ',  $e->getMessage(), "\n";
                    die();
                }

                // - Выполняем запрос
                try {
                    $arrResult = $PDO->query($sQuery)->fetchAll(PDO::FETCH_ASSOC);
                } catch (Exception $e) {
                    echo 'Caught exception: ',  $e->getMessage(), "\n";
                }

                // - Отключаемся
                $PDO = null;

                return $arrResult;
            }

            function insert(String $sQuery = '')
            {
                // Подключаемся
                try {
                    $PDO = new PDO('mysql:host=' . $this->database_server . ';dbname=' . $this->dbase, $this->database_user, $this->database_password);
                } catch (Exception $e) {
                    echo 'Caught exception: ',  $e->getMessage(), "\n";
                    die();
                }

                // - Выполняем запрос
                try {
                    $PDO->query($sQuery)->fetch(PDO::FETCH_ASSOC);
                } catch (Exception $e) {
                    echo 'Caught exception: ',  $e->getMessage(), "\n";
                }
                $arrResult = $PDO->lastInsertId();

                // - Отключаемся
                $PDO = null;

                return $arrResult;
            }
        }
    }

    if (!class_exists('ReservationT')) {

        class ReservationT extends DataBase
        {
            public $id = '';
            public $date = '';
            public $hour = '';
            public $place = '';
            public $sTableName = 'reservation_t';

            public $dHourWorkStart = '';
            public $dHourWorkStop = '';
            public $fHourInterval = '';

            function get(String $sDate = '')
            {
                return $this->get_day($sDate);
            }

            // Вывод совокупных данныех по пронированию за день
            function get_day(String $sDate = '')
            {
                $arrResult = [];

                // Получаем шаблон дня
                $arrResultTemplate = $this->get_day_template($sDate);

                // Получаем данные из базы
                $arrResultDatebase = $this->get_day_db($sDate);

                if (isset($arrResultDatebase[-1])) $arrResult['disabled_day'] = $arrResultDatebase[-1]['id'];
                else $arrResult['disabled_day'] = 0;

                // Отмечаем забронированные
                foreach ($arrResultTemplate as &$arrResultItem) {
                    // Если забронирован
                    if (isset($arrResultDatebase[$arrResultItem['hour']])) {
                        $arrResultItem['check'] = true;
                        $arrResultItem['id'] = $arrResultDatebase[$arrResultItem['hour']]['id'];
                    }

                    $arrResult[] = $arrResultItem;
                }

                return $arrResult;
            }

            // Структура дня основываясь на данных в настройках
            function get_day_template(String $sDate = ''): array | string
            {
                $arrResult = [];

                $iCurrentHour = $this->dHourWorkStart;

                while ($iCurrentHour <= $this->dHourWorkStop) {
                    $arrResult[$iCurrentHour] = array(
                        'id' => 0,
                        'hour' => $iCurrentHour,
                        'date' => $sDate,
                        'place' => '0',
                    );

                    // Если добавлять ещё пол часа
                    if ((int)$this->fHourInterval) {
                        $arrResult[$iCurrentHour . '.3'] = array(
                            'id' => 0,
                            'hour' => $iCurrentHour . '.3',
                            'date' => $sDate,
                            'place' => '0',
                        );
                    }

                    $iCurrentHour++;
                }

                return $arrResult;
            }

            // Данные по проням из базы
            function get_day_db(String $sDate = ''): array | string
            {
                $arrResult = [];

                $sQuery = " SELECT * FROM $this->sTableName ";
                if ($sDate)
                    $sQuery .= " WHERE `date` = '$sDate' ";

                $arrResultDatebase = $this->query_all($sQuery);

                foreach ($arrResultDatebase as $arrResultDatebaseItem)
                    $arrResult[(string)$arrResultDatebaseItem['hour']] = $arrResultDatebaseItem;

                return $arrResult;
            }

            function add(): bool | int
            {
                global $modx;
                // Пользователь не авторизирован, ошибка
                if (!$modx->user->isAuthenticated()) return false;
                $sQuery = " INSERT INTO `$this->sTableName` (`id`, `date`, `hour`, `place`) VALUES (NULL, '$this->date', '$this->hour', '$this->place'); ";
                $iId = $this->insert($sQuery);
                if ($iId) return $iId;
                else return false;
            }

            function del(Int $sId = 0): bool
            {
                global $modx;
                if (!$modx->user->isAuthenticated()) return false;
                $sQuery = " DELETE FROM `$this->sTableName` WHERE `id` = $sId ";

                if (!$this->query($sQuery)) return true;
                else return false;
            }

            function get_disabled_days()
            {
                $arrResult = [];

                $sDate = date('Y-m-d');

                $sQuery = " SELECT * FROM $this->sTableName ";
                if ($sDate)
                    $sQuery .= " WHERE `date` >= '$sDate' AND `hour` < 0 ";

                $arrResultDatebase = $this->query_all($sQuery);

                foreach ($arrResultDatebase as $arrResultDatebaseItem)
                    $arrResult[] = $arrResultDatebaseItem;

                return $arrResult;
            }

            function get_disabled_times()
            {
                $arrResult = [];

                $sDate = date('Y-m-d');

                $sQuery = " SELECT * FROM $this->sTableName ";
                if ($sDate)
                    $sQuery .= " WHERE `date` >= '$sDate' AND `hour` >= 0 ";

                $arrResultDatebase = $this->query_all($sQuery);

                foreach ($arrResultDatebase as $arrResultDatebaseItem)
                    $arrResult[] = $arrResultDatebaseItem;

                return $arrResult;
            }


            function __construct(String $dHourWorkStart = '', String $dHourWorkStop = '', String $fHourInterval = '')
            {
                $this->dHourWorkStart = $dHourWorkStart;
                $this->dHourWorkStop = $dHourWorkStop;
                $this->fHourInterval = $fHourInterval;
            }
        }
    }
}

// ВЫВОД интерфейса
if (true) {
    if (isset($bShowInterfase) && $bShowInterfase === 'true') {
        return $modx->getChunk('reservation_t', array(
            'name' => 'John',
        ));
    }
}

// ОБРАБОТКА ЗАПРОСОВ
if (true) {
    if (isset($sEvent)) {
        $arrResults['text'] = 'Success';
        $arrResults['error'] = '';
        $arrResults['data'] = '';

        switch ($sEvent) {
            case 'show': # Вывод
                $oReservationT = new ReservationT(
                    $dHourWorkStart,
                    $dHourWorkStop,
                    $fHourInterval
                );
                $arrResults['data'] = $oReservationT->get($arrRequestData['date']);
                die(json_encode($arrResults));
                break;
            case 'options': # Поля для бронирования
                $oReservationT = new ReservationT(
                    $dHourWorkStart,
                    $dHourWorkStop,
                    $fHourInterval
                );
                $arrResults['data'] = $oReservationT->get($arrRequestData['date']);
                foreach ($arrResults['data'] as &$arrData) {
                    if ($arrData['id']) unset($arrData);
                    
                    $sTimeFormat = str_replace('.', ':', $arrData['hour']);
                    $sTimeFormat .= '0';
                    $arrData['hour'] = $sTimeFormat;
                }
                die(json_encode($arrResults));
                break;

            case 'add': # Добавление
                if (empty($arrRequestData)) return $arrResults['error'] = 'Not data for add';

                $oReservationT = new ReservationT();
                $oReservationT->date = $arrRequestData['date'];
                $oReservationT->hour = $arrRequestData['hour'];
                if (isset($arrRequestData['place'])) $oReservationT->place = $arrRequestData['place'];
                if (isset($arrRequestData['fullday'])) $oReservationT->hour = -1;

                $iId = $oReservationT->add();
                if ($iId) {
                    $arrResults['data'] = array(
                        'id' => $iId
                    );
                } else {
                    http_response_code(503);
                    $arrResults['error'] = 'Error add';
                }

                die(json_encode($arrResults));
                break;

            case 'del': # Удаление
                if (empty($arrRequestData)) return $arrResults['text'] = $arrResults['error'] = 'Not data for add';

                $oReservationT = new ReservationT();

                if (!$oReservationT->del($arrRequestData['id'])) {
                    http_response_code(503);
                    $arrResults['error'] = 'Error del';
                }
                die(json_encode($arrResults));
                break;
            case 'show_disabled_days': # Вывод отключенных дней
                $sResult = '';
                $oReservationT = new ReservationT();
                $arrDisabledDays = $oReservationT->get_disabled_days();
                foreach ($arrDisabledDays as $addDisabledDay) {
                    // Преобразование строки в объект даты
                    $date = date_create_from_format('Y-m-d', $addDisabledDay['date']);

                    // Форматирование объекта даты в нужный формат
                    $formatted_date = date_format($date, 'n-j-Y');

                    $sResult .= '"' . $formatted_date . '",';
                }
                return '[' . mb_substr($sResult, 0, -1) . ']';
                break;
            case 'show_disabled_times': # Вывод забронированного времени
                $sResult = '';
                $oReservationT = new ReservationT();
                $arrDisabledTimes = $oReservationT->get_disabled_times();
                foreach ($arrDisabledTimes as $addDisabledTime) {

                    // Преобразование строки в объект даты
                    $date = date_create_from_format('Y-m-d', $addDisabledTime['date']);

                    // Форматирование объекта даты в нужный формат
                    $iYear = (int)date_format($date, 'Y');
                    $iMonth = (int)date_format($date, 'n');
                    $iDay = (int)date_format($date, 'j');

                    if (strripos($addDisabledTime['hour'], '.') !== false) {
                        $sTimeFormat = str_replace('.', ':', $addDisabledTime['hour']);
                        $sTimeFormat .= '0';
                    } else {
                        $sTimeFormat = $addDisabledTime['hour'];
                    }

                    $sResult .= '
                        if (date[2] == ' . $iYear . ') { //В определенный год
                            if (date[1] == ' . $iMonth . ') { //В определенный месяц
                                if (date[0] == ' . $iDay . ') { // В определённый день
                                    arrBlockTime["' . $sTimeFormat . '"] = true
                                }
                            }
                        }
                    ';
                }
                return $sResult;
                break;
        }
    }
}
