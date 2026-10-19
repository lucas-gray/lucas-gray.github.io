<?php
// Load the helper functions once. __DIR__ is this file's folder, and the dot
// joins it to '/fans.php'. Loading the functions does not call them.
require_once __DIR__ . '/fans.php';

// Setup: follow config.example.php, then run the SQL in instructions.md in your database.
$userApiUrl = 'https://randomuser.me/api/';
$albumApiUrl = 'https://csci331vm.cs.montana.edu/~h48c334/api/random_album.php';

// Start with an empty list so the table can render before database work is added.
$fans = [];
$message = 'Starter: database access and API requests are not implemented yet.';

// PHP runs this file again for every request. Opening the page normally sends
// GET; clicking a submit button sends POST to this same page. PHP runs on the
// server, then sends the resulting HTML to the browser.
//
// TODO: Complete the request handling here, before any HTML is sent.
// 1. Preserve feedback between requests. Ordinary variables such as $message
//    do not survive a redirect. A session keeps values for the same visitor:
//    call session_start() before using $_SESSION and before any HTML output.
//    Save the result in $_SESSION['message'] before redirecting. On the next
//    GET, copy it to $message and unset that session entry so it displays once.
//
// 2. Open a connection with openDatabase() and store it in $db. Put database
//    and API calls inside try { ... }. If a helper throws an exception (an
//    error), PHP skips the remaining try code and enters a matching catch.
//    catch (FanApiException $error) can show $error->getMessage(), which is a
//    message intended for the user. A later catch (Throwable $error) handles
//    other errors: show a general database/setup message, not raw error details.
//    A failed API request must not continue to insertFan().
//
// 3. Check whether $_SERVER['REQUEST_METHOD'] is 'POST'. Read the clicked
//    button with $_POST['action'] ?? ''; ?? supplies '' if no action was sent.
//    - 'add': call requestJson() for each API, combine the results with
//      fanFromResponses(), and pass that fan record and $db to insertFan().
//    - 'delete': call deleteOldestFan($db), which you must implement in fans.php.
//      Its true/false result tells you whether a row was deleted or none existed.
//      Do not request either API in this branch.
//    - Anything else: report an unknown action without changing the database.
//
// 4. Save the POST result message, then redirect with header('Location: index.php',
//    true, 303) and exit. The browser follows with a GET, so refreshing that
//    page does not repeat the POST. header() must run before any HTML output;
//    exit stops this request from continuing into the table code below.
//
// 5. On GET, use readFans($db) to fill $fans. The foreach loop below displays
//    one table row per fan. A GET only reads data; it must not add or delete fans.
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Build a Fan Database</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <h1>Build a Fan Database</h1>
    <!-- The short PHP echo tag prints a value. escapeHtml() displays it as text. -->
    <p role="status"><?= escapeHtml($message) ?></p>
    <!-- With no action URL, this form submits to the current page. The clicked
         button sends action=add or action=delete in the POST request. -->
    <form method="post">
        <button type="submit" name="action" value="add">Add one fan</button>
        <button type="submit" name="action" value="delete">Delete oldest fan</button>
    </form>
    <table>
        <thead>
            <tr>
                <th scope="col">First name</th>
                <th scope="col">Age</th>
                <th scope="col">Album</th>
            </tr>
        </thead>
        <tbody>
            <!-- foreach visits each saved record in $fans, calling it $fan.
                 The colon/endforeach syntax lets HTML sit inside the PHP loop. -->
            <?php foreach ($fans as $fan): ?>
                <!-- TODO: Add one tr containing three td cells. Show $fan's
                     first_name, age, and album values using escapeHtml(). -->
            <?php endforeach; ?>
        </tbody>
    </table>
</body>
</html>
