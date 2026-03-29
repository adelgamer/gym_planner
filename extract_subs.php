<?php
$json = json_decode(file_get_contents('d:/dev/gym_planner/public/exercises_with_muscles_subdivisions.json'));
$subs = [];
foreach ($json as $ex) {
    if (isset($ex->primarySubdivionMuscles))
        $subs = array_merge($subs, $ex->primarySubdivionMuscles);
    if (isset($ex->secondarySubdivionMuscles))
        $subs = array_merge($subs, $ex->secondarySubdivionMuscles);
}
$uniqueSubs = array_unique($subs);
sort($uniqueSubs);
echo "START_SUBS\n";
foreach ($uniqueSubs as $sub) {
    echo $sub . "\n";
}
echo "END_SUBS\n";
