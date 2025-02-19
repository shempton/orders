<?php
// Подключение к базе данных
$db = new PDO('sqlite:database.db');

// Создание таблиц, если они не существуют
$db->exec('
CREATE TABLE IF NOT EXISTS clients (
    id INTEGER PRIMARY KEY,
    name TEXT NOT NULL
)
');

$db->exec('
CREATE TABLE IF NOT EXISTS merchandise (
    id INTEGER PRIMARY KEY,
    name TEXT NOT NULL
)
');

$db->exec('
CREATE TABLE IF NOT EXISTS orders (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    item_id INTEGER NOT NULL,
    customer_id INTEGER NOT NULL,
    comment TEXT,
    status TEXT DEFAULT "new",
    order_date DATE DEFAULT CURRENT_DATE,
    FOREIGN KEY (item_id) REFERENCES merchandise(id),
    FOREIGN KEY (customer_id) REFERENCES clients(id)
)
');

// Функция для проверки валидности строки
function isValidRow($row) {
    return count($row) === 3 && is_numeric($row[0]) && is_numeric($row[1]);
}

// Функция для загрузки данных из файла
function loadOrders($filePath, $db) {
    $validOrders = [];
    $invalidRows = [];

    if (($handle = fopen($filePath, 'r')) !== FALSE) {
        while (($data = fgetcsv($handle, 1000, ';')) !== FALSE) {
            if (isValidRow($data)) {
                $validOrders[] = [
                    'item_id' => (int)$data[0],
                    'customer_id' => (int)$data[1],
                    'comment' => $data[2]
                ];
            } else {
                $invalidRows[] = $data;
            }
        }
        fclose($handle);
    }

    // Запись невалидных строк в файл
    $invalidFile = fopen('invalid_rows.txt', 'w');
    foreach ($invalidRows as $row) {
        fputcsv($invalidFile, $row, ';');
    }
    fclose($invalidFile);

    // Вставка валидных заказов в базу данных
    $stmt = $db->prepare('
    INSERT INTO orders (item_id, customer_id, comment)
    VALUES (:item_id, :customer_id, :comment)
    ');

    foreach ($validOrders as $order) {
        $stmt->execute($order);
    }
}

// Пример использования
loadOrders('orders.txt', $db);

// Закрытие соединения с базой данных
$db = null;