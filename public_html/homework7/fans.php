<?php
/*
 * PHP reading guide: names beginning with $ are variables. A type before a
 * parameter describes its input; the type after ): describes the return value.
 * string = text, array = a list or a collection of named values, bool = true or
 * false, and void = no returned value. mysqli is a database connection object;
 * -> calls an object's method (function) or accesses one of its properties.
 * @param and @return below are documentation labels, not executable code.
 * A return value has no required variable name: the caller chooses where to
 * store it. Each comment identifies the local variable or expression returned.
 */

/**
 * Purpose: Display a value as text in HTML, rather than interpreting it as HTML.
 * How: Convert the value to text, then replace special characters such as <,
 * >, &, and quotes with HTML entities. For example, < becomes &lt;.
 *
 * @param mixed $value Value to display, normally a string (text) or integer.
 *     mixed means no single input type is required by this function signature.
 * @return string Escaped text, returned directly from htmlspecialchars();
 *     there is no local return variable.
 * @see https://www.php.net/manual/en/function.htmlspecialchars.php
 */
function escapeHtml($value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

/**
 * Purpose: Open a connection to the configured MySQL or MariaDB database.
 * How: Load credentials from the private configuration file, create a mysqli
 * connection, and use utf8mb4 so names and albums can contain Unicode text.
 * Database errors throw exceptions, which index.php handles with catch.
 *
 * Parameters: None. Connection settings come from the configuration file.
 * @return mysqli $db The open database connection object used by other helpers.
 * @see https://www.php.net/manual/en/mysqli.construct.php
 */
function openDatabase(): mysqli
{
    $config = require dirname(__DIR__, 3) . '/private/fan-db-config.php';
    mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
    $db = new mysqli($config['host'], $config['user'], $config['password'],
        $config['database'], $config['port'] ?? 3306);
    $db->set_charset('utf8mb4');
    return $db;
}

// Give API errors a distinct type so index.php can catch them separately.
// "extends" inherits RuntimeException's constructor and getMessage() method.
// The empty {} adds no behavior; this class only gives the error a new type.
// https://www.php.net/manual/en/class.runtimeexception.php
// https://www.php.net/manual/en/language.oop5.inheritance.php
class FanApiException extends RuntimeException {}

/**
 * Purpose: Request JSON from an API and turn it into data PHP can use.
 * How: Use cURL (PHP's HTTP request library) to fetch the URL with a 15-second
 * limit. Check for a successful HTTP status, then decode the response text.
 * json_decode(..., true) turns JSON objects into arrays with named keys.
 * A failed request or unusable JSON throws FanApiException for index.php to catch.
 *
 * @param string $url The complete API address, as text.
 * @return array $data The decoded response, accessed by keys or list positions.
 * @see https://www.php.net/manual/en/function.curl-exec.php
 * @see https://www.php.net/manual/en/function.json-decode.php
 */
function requestJson(string $url): array
{
    $curl = curl_init($url);
    curl_setopt_array($curl, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => 15,
    ]);
    $body = curl_exec($curl);
    $status = curl_getinfo($curl, CURLINFO_RESPONSE_CODE);
    if ($body === false || $status < 200 || $status >= 300) {
        throw new FanApiException('Could not fetch API data. Check the API URL and try again.');
    }
    $data = json_decode($body, true);
    if (!is_array($data)) {
        throw new FanApiException('The API did not return valid JSON data.');
    }
    return $data;
}

/**
 * Purpose: Combine one random user and one album into a fan record.
 * How: Take the first user and album (list position 0), map their fields to
 * database column names, and check required values before returning the record.
 * The ?? operator supplies a fallback when a key is missing or its value is null.
 * Missing required data throws FanApiException, so the caller does not insert it.
 *
 * @param array $users Decoded user response; the first user is in ['results'][0].
 * @param array $albumResponse Decoded album response; the first album is in ['album'][0].
 * @return array $fan Named values for first_name, last_name, age, album, year,
 *     artist, and picture. For example, $fan['first_name'] holds the first name.
 * @see https://www.php.net/manual/en/language.types.array.php
 */
function fanFromResponses(array $users, array $albumResponse): array
{
    $user = $users['results'][0] ?? [];
    $album = $albumResponse['album'][0] ?? [];
    $fan = [
        'first_name' => $user['name']['first'] ?? null,
        'last_name' => $user['name']['last'] ?? null,
        'age' => $user['dob']['age'] ?? null,
        'album' => $album['title'] ?? null,
        'year' => $album['year'] ?? null,
        'artist' => $album['artist'] ?? null,
        'picture' => $user['picture']['medium'] ?? null,
    ];
    foreach ($fan as $value) {
        if (!is_scalar($value) || trim((string) $value) === '') {
            throw new FanApiException('The API response is missing required fan or album data.');
        }
    }
    return $fan;
}

/**
 * Purpose: Save one fan record in the fan_base table.
 * How: Prepare an INSERT with ? placeholders, bind the seven values, execute it,
 * and close the statement. Binding keeps data separate from SQL commands.
 * In bind_param's type string, s means string (text) and i means integer.
 *
 * @param mysqli $db An open database connection returned by openDatabase().
 * @param array $fan The seven named field values returned by fanFromResponses().
 * @return void No value or return variable; the result is a new database row.
 * @see https://www.php.net/manual/en/mysqli.prepare.php
 */
function insertFan(mysqli $db, array $fan): void
{
    $statement = $db->prepare(
        'INSERT INTO fan_base (first_name, last_name, age, album, year, artist, picture)
         VALUES (?, ?, ?, ?, ?, ?, ?)'
    );
    $statement->bind_param('ssisiss', $fan['first_name'], $fan['last_name'],
        $fan['age'], $fan['album'], $fan['year'], $fan['artist'], $fan['picture']);
    $statement->execute();
    $statement->close();
}

/**
 * Purpose: Remove the earliest-added fan, not the fan with the greatest age.
 * How: Delete at most one row, choosing the lowest remaining auto-increment ID.
 * Then check affected_rows, the number of rows changed by that database command.
 *
 * @param mysqli $db An open database connection returned by openDatabase().
 * @return bool true if one row was deleted; false if the table was empty.
 *     The comparison $db->affected_rows === 1 is returned directly, without
 *     a local return variable. === checks equality of both value and type.
 * @see https://www.php.net/manual/en/mysqli.query.php
 */
function deleteOldestFan(mysqli $db): bool
{
    // TODO: Delete the row with the lowest remaining ID using one DELETE query.
    // Return true if one row was deleted, or false if the table was empty.
}

/**
 * Purpose: Get all saved fans in the order they were added.
 * How: SELECT the rows in ascending ID order, then fetch them as PHP arrays.
 * MYSQLI_ASSOC uses column names as keys, such as 'first_name', instead of numbers.
 *
 * @param mysqli $db An open database connection returned by openDatabase().
 * @return array A list of fan rows, or an empty list if there are no fans.
 *     Each row is an array of named column values. The result of fetch_all()
 *     is returned directly; index.php stores this list in its $fans variable.
 * @see https://www.php.net/manual/en/mysqli-result.fetch-all.php
 */
function readFans(mysqli $db): array
{
    $result = $db->query('SELECT * FROM fan_base ORDER BY id');
    return $result->fetch_all(MYSQLI_ASSOC);
}
