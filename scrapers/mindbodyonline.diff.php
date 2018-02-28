<?

$ar1 = file("mindbodyonline.new.csv");
$ar2 = file("mindbodyonline.old.csv");



var_dump(sizeof(array_diff($ar1,$ar2)));
$diff1 = array_diff($ar1,$ar2);

print_R( $diff1 );
