<?php
require_once 'db.php';

class Payment extends NewDB
{

/**
 * Метод /register
 * ожидает 2 параметра
 * сумму и назначение платежа писать через "/"
 * сумма принимаеца в виде INT
 * назначение платежа в виде STRTING
 * выводит json с сслыкой URL
 */
    public function register(int $sum, string $purpose)
    {
        // Генерируем случайное число. Случайное число будет идентификатором сесси
        $sessionId = mt_rand().''.mt_rand();
        // Передаю данные другому классы, чтобы записать данные в базу
        NewDB::saveOrder($purpose, $sessionId, $sum);
        //Создаю текст в виде URL и отправляю
        // в тексте есть URL параметр с id сессии
        $url =  'http://avito.test/?sessionId='.$sessionId;
        header('Content-Type: application/json');
        // вывыожу как тело заголовка
        echo json_encode($url);
    }

    /**
     * @return array
     * вспомогательный метод для получения массива от URI с разделителем "/"
     * здест будут делится через слеш сумма и назначение платежа
     */

    private function getURI()
    {
        if (!empty($_SERVER['REQUEST_URI'])) {
            $uri =  trim($_SERVER['REQUEST_URI']);
            return explode('/', $uri);
        }
    }

    /**\
     * передаю данные из URI в метод register
     * и возвращяю метод register
     */
    public function run()
    {
        $uri = $this->getURI();
        if($uri['1'] == 'register'){
            return $this->register($uri[2],$uri[3]);
        }elseif($uri['1'] == 'order'){
            return $this->getOrderBySessionId($uri[2]);
        }
    }

    /**
     * @throws Exception
     * Этот метод для того, чтобы ограничтить время жизни сесси платежа
     * ограничевается на 30 минут
     */
    public function getSession()
    {
        //проверяю есть ли id в GET
        if($_GET['sessionId']) {
            $order = NewDB::getOrder($_GET['sessionId']);
            $sum = $order[0]['sum'];
            $purpose = $order[0]['purpose'];
            $now_date = new DateTime(date('Y-m-d H:i'));
            $old_date = new DateTime($order[0]['datetime']);
            $interval = $now_date->diff($old_date);
            $hours = $interval->format("%H");
            $minutes = $interval->format("%I");
            if(!$hours <= 1)
            if($minutes >= 30){
                echo 'Извините, вермя ссылки истекло';
            } else {
                include 'payments.php';
                $this->post();
            }
        }
    }

    /**
     * @param $s
     * @return bool
     * сслыка на сайт где нашёл этот алгоритм https://artkiev.com/blog/php-algoritm-credit-card.htm
     */
    private function is_valid_credit_card($s)
    {
        // оставить только цифры
        $s = strrev(preg_replace('/[^\d]/', '', $s));

        // вычисление контрольной суммы
        $sum = 0;
        for ($i = 0, $j = strlen($s); $i < $j; $i++) {
            // использовать четные цифры как есть
            if (($i % 2) == 0) {
                $val = $s[$i];
            } else {
                // удвоить нечетные цифры и вычесть 9, если они больше 9
                $val = $s[$i] * 2;
                if ($val > 9) $val -= 9;
            }
            $sum += $val;
        }

        // число корректно, если сумма равна 10
        return (($sum % 10) == 0);
    }

    /**
     * небольшая проверка и
     * имитация успешной оплаты
     *
     */
    private function post()
    {
        if(!empty($_POST['submit'])){
            if(!empty(NewDB::getOrder($_GET['sessionId']))){
                if ($this->is_valid_credit_card($_POST['card'])) {
                    if(NewDB::condition($_GET['sessionId'])){
                        echo "Имитиция успешной оплаты произошла успешно:)";
                    } else echo 'Пожалуйста введи правильные данные карты';
                }
            }
        }

    }

    /**
     * @param $sessionId
     * передаётся id сессии и модно оплучить все данные о сесии
     * в виде json
     */
    public function getOrderBySessionId($sessionId)
    {
       echo json_encode( NewDB::getOrder($sessionId));
    }


}


$db = new NewDB();
$a = new Payment();
$a->run();
$a->getSession();


