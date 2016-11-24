Тестовое задание (Кораблик)
===========================

Предварительные замечания
-------------------------
В задании реализованы несколько классов в соответствии с требованиями поставленных задач по PHP. В качесвтве примера написан скрипт index.php, который демонстрирует базовые возможности использования реализованной функциональности.

Использование скрипта index.php для просмотра результатов
---------------------------------------------------------

- скачать репозиторий в выбранную вами директорию (напр., в директорию korablik)
- для быстроты развертки и удобства достаточно запустить в ней built-in PHP serser (PHP>=5.4.0):
```
cd /home/user/projects/korablik
php -S localhost:8000

PHP 7.0.12 Development Server started at Thu Nov 22 20:28:10 2016
Listening on http://localhost:8000
Document root is /home/user/projects/korablik
Press Ctrl-C to quit.
```
- открыть в браузере URL http://localhost:8000 для просмотра результатов

MySQL
-----

Один из вариантов решения задачи может быть следующим: 

```sql
SELECT 
  c.Name, SUM(p.Price * p.Count) AS total_amount, count(o.Id) as order_count
FROM 
  clients c
LEFT JOIN orders o ON o.Clients_id = c.Id
LEFT JOIN products p ON p.Order_id = o.Id
WHERE 
(
    p.Id IN (151515,151617,151514) AND
    o.Ctime >= UNIX_TIMESTAMP('2015-03-01 00:00:00') AND 
    o.Ctime < UNIX_TIMESTAMP('2015-04-01 00:00:00')
) 
OR 
(
    o.Id IS NULL AND
    # пишем именно '%@mail.ru%', т.к. по задача требует email "содержащий" @mail.ru;
    # если бы формулировка была бы "оканчивается на", то следовало бы записать LIKE '%@mail.ru' (и это помогло бы использовать индекс по полю email)   
    c.Email LIKE '%@mail.ru%'
)
GROUP BY c.Id
ORDER BY total_amount DESC
```

Javascript 
----------

Решение задачи см. в файле korablik.js

Git
---
```
git checkout -b develop
// some work...
git add .
git commit -m 'New scripts were added.'
git checkout master
git merge develop
```

