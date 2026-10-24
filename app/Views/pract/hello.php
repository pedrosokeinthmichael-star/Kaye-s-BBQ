
<html>  

<body>
<?php
  //  echo ("<h1> Hellow World </h1>");

    /*print "Hello, World!";
    
    print ("Hello, World!");*/

    //print "Hello, World!";  

/*
$var = "Hello, World!";

if ($var) {
  
    print_r($var);
    //var_dump($var);
    echo "yawa";
}
*/

/*
$arr = array("apple", "banana", "pineapple");

echo $arr[3];
*/
/*
class Car  {
    function __construct(){
        $this-> model = "HELLOW";
    }
}

$herbie = new Car();
echo $herbie->model;
*/

$name = "Pedroso";
$age = 1;

if($age >= 30){
    echo "major";
    echo "<br>";
    echo '$name';
}
else{
    echo "minor";
    echo "<br>";
    echo "$name";
}

/*
$new_str = str_replace("Hello", "Hi", "Hello World!"); // Outputs: Hi World!

echo $new_str;
*/
$part = substr("Hello World", 6,10); // Outputs: World
echo $part;

$pos = strpos("Hello World", "World"); // Outputs: 6
echo $pos;


$str1 = "123";
$str2 = "321";
$sum = $str1 + $str2; // Outputs:579
echo "<br>";
echo $sum;


$str = "hello";

if(is_numeric($str)){
    echo "<br>";
    echo "Numeric";
}
else{
    echo "<br>";
    echo "Not Numeric";
}




$num1 = 5;
$num1 += 5;
echo "<br>";
echo $num1;
/*
function gwapo(){
    $gwapo = "Gwapo pedroso ";
    echo $gwapo;
    $count = 0;
    $count++;
    echo "<br>";
    echo $count;

}

function pogi(){

    global $name;

    $pogi ="pogi ko ";
    echo $pogi;
    echo "<br>";
    echo gwapo();


    echo $name;
}



pogi();*/


?>