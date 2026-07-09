<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Kalkulator BMI</title>
    <link href='style.css' rel='stylesheet'>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 40px;
        }
        form {
            background-color: #f4f4f4;
            padding: 20px;
            border-radius: 8px;
            width: 300px;
        }
        input[type="number"], input[type="submit"], button {
            width: 100%;
            padding: 10px;
            margin-top: 5px;
            box-sizing: border-box;
            border: none;
        }
        input[type="submit"], button {
            background-color: #5cb85c;
            color: white;
            border-radius: 5px;
            cursor: pointer;
        }
        input[type="submit"]:hover, button:hover {
            background-color: #4cae4c;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        table, th, td {
            border: 1px solid black;
        }
        th, td {
            padding: 8px;
            text-align: left;
        }
    </style>
</head>
<body>
    <img src="bmi-skale_dla_zdrowia.png" alt="Skala dla zdrowia BMI">
    <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
        Pleć: 
        <input type="radio" id="kobieta" name="Plec" value="Kobieta">
        <label for="kobieta">Kobieta</label><br>
        <input type="radio" id="mezczyzna" name="Plec" value="Mężczyzna">
        <label for="mezczyzna">Mężczyzna</label><br>
        Wiek: <input type="number" name="Wiek" id="Wiek"> lat<br>
        Waga: <input type="number" name="weight" id="Waga"> kg<br>
        Wzrost: <input type="number" name="height" id="Wzrost"> cm<br>
        <button type="button" onclick="Oblicz()">Obliczyć</button>
        <input type="submit" name="calculate" value="Zapisz pomiar">
    </form>
    <div id="result"></div>

    <?php
    $conn = new mysqli('localhost', 'root', '', 'dane2');
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['calculate'])) {
        $weight = $_POST['weight'];
        $height = $_POST['height'];
        if ($height > 0) {
            $heightInMeters = $height / 100;
            $bmidb = $weight / ($heightInMeters * $heightInMeters);
            $stmt = $conn->prepare("INSERT INTO pomiary (waga, wzrost) VALUES (?, ?)");
            $stmt->bind_param("dd", $weight, $height);
            $stmt->execute();
            $stmt->close();
            echo "<h3>Twoje BMI wynosi: " . number_format($bmidb, 2) . "</h3>";
        } else {
            echo "<h3>Wprowadź poprawne wartości.</h3>";
        }
    }

    if (isset($_POST['showHistory'])) {
        $result = $conn->query("SELECT id_pomiaru, waga, wzrost, data_pomiaru FROM pomiary ORDER BY data_pomiaru DESC");
        if ($result->num_rows > 0) {
            echo "<table>
                <tr><th>Nr pomiaru</th><th>Waga</th><th>Wzrost</th><th>Data pomiaru</th></tr>";
            while ($row = $result->fetch_assoc()) {
                echo "<tr><td>" . $row["id_pomiaru"] . "</td><td>" . $row["waga"] . "</td><td>" . $row["wzrost"] . "</td><td>" . $row["data_pomiaru"] . "</td></tr>";
            }
            echo "</table>";
        } else {
            echo "<p>Brak historii pomiarów.</p>";
        }
    }
    $conn->close();
    ?>
    
    <form action="" method="post">
        <input type="submit" name="showHistory" value="Historia pomiarów">
    </form>

    <script>
        function Oblicz() {
            var Wiek = parseInt(document.getElementById("Wiek").value);
            var Waga = parseFloat(document.getElementById("Waga").value);
            var Wzrost = parseFloat(document.getElementById("Wzrost").value);
            var Plec = document.querySelector('input[name="Plec"]:checked');

            if (!Wiek || !Waga || !Wzrost || !Plec) {
                alert("Proszę wpisać dane");
                return;
            }

            if (Wiek < 0 || Waga < 0 || Wzrost < 0) {
                alert("Wiek, waga i wzrost muszą być >= 0");
                return;
            }

            if (Wiek < 18) {
                alert("Proszę wpisać prawidłowy Wiek, który musi być >= 18");
                return;
            }

            if (!Plec) {
                alert("Proszę wybrać płeć");
                return;
            }

            var bmi = Waga / (Wzrost / 100) ** 2;
            var PlecLabel = Plec.value === "Kobieta" ? "Kobiety" : "Mężczyzny";
            var resultDiv = document.getElementById("result");

            resultDiv.textContent = `BMI dla ${PlecLabel} w wieku ${Wiek} wynosi: ${bmi.toFixed(2)}`;

            if (bmi < 16.5) {
                resultDiv.classList.add('Wygłodzenie');
            } else if (bmi >= 16.5 && bmi < 17) {
                resultDiv.classList.add('Wychudzenie');
            } else if (bmi >= 17 && bmi < 18.5) {
                resultDiv.classList.add('Niedowaga');
            } else if (bmi >= 18.5 && bmi < 25) {
                resultDiv.classList.add('Optimum');
            } else if (bmi >= 25 && bmi < 30) {
                resultDiv.classList.add('Nadwaga');
            } else if (bmi >= 30 && bmi < 35) {
                resultDiv.classList.add('Otyłość 1 st');
            } else if (bmi >= 35 && bmi < 40) {
                resultDiv.classList.add('Otyłość 2 st');
            } else if (bmi >= 40) {
                resultDiv.classList.add('Otyłość 3 st');
            }
        }
    </script>
</body>
</html>