<div>
    <h2>Задача</h2>
</div><br />
<div>
    веб сервис, который будет отвечать за расчет рабочего расписания сотрудников
</div> <br />

<div>
    В ответ должно отдать рабочее расписание работника в формате JSON.<br />
    В расписание не должны попасть:<br />
    <ul>
    <li> праздничные дни </li>
    <li> отпускные дни сотрудника </li>
    <li> обеденный перерыв </li>
    <li> время до начала рабочего дня и после рабочего дня </li>
    <li> время внутренних мероприятий </li>
    </ul>
</div><br />
<br />
<div>
    <h2>Путь для запросов и формат ответа</h2><br />
    /api/worker/id/?start_date=xx.xx.xxxx&end_date=xx.xx.xxxx - реализованный адрес для получения данных<br />
    <br />
    формат ответа (json)<br />
    {<br />
        &nbsp;&nbsp;&nbsp;&nbsp;'day' => 'Y-m-d',<br />
        &nbsp;&nbsp;&nbsp;&nbsp;'timeRange' => {<br />
            &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;{<br />
                &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'start' => H:i,<br />
                &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'end' => H:i<br />
            &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;},<br />
            &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;...<br />
        &nbsp;&nbsp;&nbsp;&nbsp;}<br />
    }, ...<br />
</div><br />
<br />
<div>
    <h2>Используемые открытые источники</h2><br />
    https://isdayoff.ru - сервис для получения производственного календаря. (открытый источник)
</div><br />
<div>
    <h2>Инструкция по запуску</h2><br />
    <br />
    Для первого запуска требуется выполнить пускты с 1 по 5 <br />
    Для последующий пунктов выполнить пункты с 1 по 2 <br />
    <br />
    1)зайти в директорию докера проекта ./docker/<br />
    2)выполнить команду docker-compose up -d (запустить контейнер)<br />
    3)зайти в консоль контейнера docker-compose exec workspace bash<br />
    4)выполнить php artisan key:generate для генерации APY_KEY<br />
    5)выполнить миграцию и заполнение бд тестовыми данными php artisan migrate --seeds
</div>