#!/usr/bin/php
<?php
require dirname(__FILE__) . '/inc/cli.php';
                
$variants = array(
    array("hour", 3600),
    array("day", 3600*24),
    array("3 days", 3600*24*3),
    array("week", 3600*24*7),
    array("month", 3600*24*30)
);

printf("           || ");
foreach ($variants as $iter) {
    list($term, $time) = $iter;
    printf("%8s | ", $term);
}
print("\n");
print(str_repeat('=', 13+11*count($variants)));
print("\n");

$q = query("SELECT uri FROM ``boards``");
while ($f = $q->fetch()) {
    printf("%10s || ", $f['uri']);
    foreach ($variants as $iter) {
        list($term, $time) = $iter;
        $qq = prepare("SELECT COUNT(*) as count FROM ``posts`` WHERE `board` = :board AND `time` > :since");
        $qq->bindValue(':board', $f['uri']);
        $qq->bindValue(':since', time()-$time, PDO::PARAM_INT);
        $qq->execute();
        $c = $qq->fetch()['count'];
        printf("%8d | ", $c);
    }
    print("\n");
}