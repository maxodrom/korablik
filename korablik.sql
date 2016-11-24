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
    # пишем именно '%@mail.ru%', т.к. по задача требует email, "содержащий" "@mail.ru";
    # если бы формулировка была бы "оканчивается на", то следовало бы записать LIKE '%@mail.ru' (и это помогло бы использовать индекс по полю email)
    c.Email LIKE '%@mail.ru%'
)
GROUP BY c.Id
ORDER BY total_amount DESC