<?php
header('Content-Type: application/json');

// Database connection
$host = 'localhost';
$db = 'local';
$user = 'root'; 
$pass = 'root'; 

try {
    // Create connection
    $pdo = new PDO("mysql:host=$host;dbname=$db", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Fetch TV shows from the database
    $stmt = $pdo->query("SELECT * FROM tv_shows");
    $tvShows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Prepare the response
    $tvShowsWithDaysLeft = array_map(function($show) {
        // Aprēķināt atlikušās dienas (ja beigu datums vēl nav pagājis)
        $end_date = new DateTime($show['end_date']);
        $now = new DateTime();
        $interval = $now->diff($end_date);
        $show['days_left'] = $interval->days;  // Pievienot atlikušās dienas

        // Latviešu mēneši
        $latvianMonths = [
            1 => 'janvāris', 2 => 'februāris', 3 => 'marts', 4 => 'aprīlis', 5 => 'maijs', 6 => 'jūnijs',
            7 => 'jūlijs', 8 => 'augusts', 9 => 'septembris', 10 => 'oktobris', 11 => 'novembris', 12 => 'decembris'
        ];

        // Formatēt sākuma un beigu datumus latviski
        $start_date = new DateTime($show['start_date']);
        $end_date = new DateTime($show['end_date']);

        // Formatējam datumu: "17. Novembris - 2. Decembris"
        $start_day = $start_date->format('d');
        $start_month = $latvianMonths[(int)$start_date->format('m')];

        $end_day = $end_date->format('d');
        $end_month = $latvianMonths[(int)$end_date->format('m')];

        // Sagatavojam formatētu datumu
        $show['formatted_date'] = $start_day . '. ' . $start_month . ' - ' . $end_day . '. ' . $end_month;

        return $show;
    }, $tvShows);

    if ($tvShowsWithDaysLeft) {
        echo json_encode([
            'status' => 'success',
            'data' => $tvShowsWithDaysLeft
        ]);
    } else {
        echo json_encode([
            'status' => 'error',
            'message' => 'No TV shows found.'
        ]);
    }

} catch (PDOException $e) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Database error: ' . $e->getMessage()
    ]);
}
?>