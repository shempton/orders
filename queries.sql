-- a. Выбрать имена всех клиентов, которые не делали заказы в последние 7 дней
SELECT c.name
FROM clients c
WHERE c.id NOT IN (
    SELECT o.customer_id
    FROM orders o
    WHERE o.order_date >= DATE('now', '-7 days')
);

-- b. Выбрать имена 5 клиентов, которые сделали больше всего заказов в магазине
SELECT c.name
FROM clients c
JOIN orders o ON c.id = o.customer_id
GROUP BY c.id
ORDER BY COUNT(o.id) DESC
LIMIT 5;

-- c. Выбрать имена 10 клиентов, которые сделали заказы на наибольшую сумму
-- Предполагаем, что у каждого товара есть цена в таблице merchandise
SELECT c.name
FROM clients c
JOIN orders o ON c.id = o.customer_id
JOIN merchandise m ON o.item_id = m.id
GROUP BY c.id
ORDER BY SUM(m.price) DESC
LIMIT 10;

-- d. Выбрать имена всех товаров, по которым не было доставленных заказов (со статусом “complete”)
SELECT m.name
FROM merchandise m
WHERE m.id NOT IN (
    SELECT o.item_id
    FROM orders o
    WHERE o.status = 'complete'
);