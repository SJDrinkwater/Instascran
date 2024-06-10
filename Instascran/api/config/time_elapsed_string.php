<?php
function time_elapsed_string($datetime, $full = false) {
    $now = new DateTime();
    $ago = new DateTime($datetime);
    $diff = $now->diff($ago);

    $string = [];

    if ($diff->y > 0) {
        $string[] = $diff->y . ' year' . ($diff->y > 1 ? 's' : '');
    }
    if ($diff->m > 0) {
        $string[] = $diff->m . ' month' . ($diff->m > 1 ? 's' : '');
    }
    if ($diff->d > 0) {
        $string[] = $diff->d . ' day' . ($diff->d > 1 ? 's' : '');
    }
    if ($diff->h > 0) {
        $string[] = $diff->h . ' hour' . ($diff->h > 1 ? 's' : '');
    }
    if ($diff->i > 0) {
        $string[] = $diff->i . ' minute' . ($diff->i > 1 ? 's' : '');
    }
    if ($diff->s > 0) {
        $string[] = $diff->s . ' second' . ($diff->s > 1 ? 's' : '');
    }

    if (!$full) {
        $string = array_slice($string, 0, 1);
    }

    return $string ? implode(', ', $string) . ' ago' : 'just now';
}
?>