<?php
session_start();

// Подключение к базе данных (замените значения на свои)
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "guestbook";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Обработка формы при отправке
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Проверка CAPTCHA
    if ($_POST["captcha"] == $_SESSION["captcha"]) {
        // Поля из формы
        $user_name = mysqli_real_escape_string($conn, $_POST["user_name"]);
        $email = mysqli_real_escape_string($conn, $_POST["email"]);
        $homepage = mysqli_real_escape_string($conn, $_POST["homepage"]);
        $captcha = mysqli_real_escape_string($conn, $_POST["captcha"]);
        $text = mysqli_real_escape_string($conn, $_POST["text"]);
        $ip_address = $_SERVER["REMOTE_ADDR"];
        $browser_info = $_SERVER["HTTP_USER_AGENT"];

        // Сохранение данных в базу данных
        $sql = "INSERT INTO guestbook (user_name, email, homepage, captcha_code, text, ip_address, browser_info)
                VALUES ('$user_name', '$email', '$homepage', '$captcha', '$text', '$ip_address', '$browser_info')";

        if ($conn->query($sql) === TRUE) {
            echo "Сообщение успешно добавлено!";
        } else {
            echo "Ошибка: " . $sql . "<br>" . $conn->error;
        }
    } else {
        echo "Неверный код CAPTCHA!";
    }
}

// Генерация случайного кода CAPTCHA
$random_number = rand(1000, 9999);

// Запись значения CAPTCHA в сессию
$_SESSION["captcha"] = $random_number;

// Вывод формы для добавления записи
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Гостевая книга</title>
</head>

<body>
<table align="center">
  <tr>
    
      <form method="post" action="">
        <label for="user_name">User Name:</label>
        <input type="text" name="user_name" id="user_name" pattern="[a-zA-Z0-9]+" required> <br>

        <label for="email">E-mail:</label>
        <input type="email" name="email" id="email" required> <br>

        <label for="homepage">Homepage:</label>
        <input type="url" name="homepage" id="homepage"> <br>

        <label for="captcha">CAPTCHA:</label>
        <input type="text" name="captcha" id="captcha" pattern="[a-zA-Z0-9]+" required>
        <img src="captcha.php" alt="CAPTCHA"> <br>

        <label for="text">Text:</label>
        <textarea name="text" id="text" required></textarea> <br>

        <input type="submit" value="Добавить">
      </form>
    
  </tr>
</table>

    <br>
    <br>
    <?php
    // Вывод сообщений в виде таблицы с сортировкой и разбиением по страницам
    $results_per_page = 25;

    if (isset($_GET["page"])) {
        $page = $_GET["page"];
    } else {
        $page = 1;
    }

    $start_from = ($page - 1) * $results_per_page;


    $sort_by = isset($_GET["sort_by"]) ? $_GET["sort_by"] : "submission_date";
    $sort_order = isset($_GET["sort_order"]) ? $_GET["sort_order"] : "desc";
    $sql = "SELECT * FROM guestbook ORDER BY $sort_by $sort_order LIMIT $start_from, $results_per_page";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        echo "<table border='1' align='center' margin-top='10'      >
        <tr>
        <th><a href='?sort_by=user_name&sort_order=asc'>user name</a></th>
        <th><a href='?sort_by=email&sort_order=asc'>e-mail</a></th>
        <th>homepage</th>
        <th><a href='?sort_by=text&sort_order=asc'>text</a></th>
        <th><a href='?sort_by=submission_date&sort_order=asc'>date</a></th>
    </tr>";

        while ($row = $result->fetch_assoc()) {
            echo "<tr>
                    <td>{$row["user_name"]}</td>
                    <td>{$row["email"]}</td>
                    <td>{$row["homepage"]}</td>
                    <td>{$row["text"]}</td>
                    <td>{$row["submission_date"]}</td>
                </tr>";
        }

        echo "</table>";

        
        $sql = "SELECT COUNT(id) AS total FROM guestbook";
        $result = $conn->query($sql);
        $row = $result->fetch_assoc();
        $total_pages = ceil($row["total"] / $results_per_page);

        echo "<div align='center'>";
        for ($i = 1; $i <= $total_pages; $i++) {
            echo "<a href='?page=$i'>$i</a> ";
        }
        echo "</div>";
    } else {
        echo "Нет сообщений в гостевой книге.";
    }

    $conn->close();
    ?>
</body>

</html>