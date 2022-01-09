<?php 

// Color Echo
function cecho($color, $text, $bgColor = "transparent"){

    $defaultColor = "0;37"; // light gray    

    $colors = [
        "black" => "0;30",
        "dGrey" => "1;30",
        "red" => "0;31",
        "lRed" => "1;31",
        "green" => "0;32",
        "lGreen" => "1;32",
        "brown" => "0;33",
        "yellow" => "1;33",
        "blue" => "0;34",
        "lBlue" => "1;34",
        "magenta" => "0;35",
        "lMagenta" => "1;35",
        "cyan" => "0;36",
        "lCyan" => "1;36",
        "lGrey" => "0;37",
        "white" => "1;37",
    ];

    $bgColors = [
        "transparent"=>"",
        "black" => ";40",
        "red" => ";41",
        "green" => ";42",
        "yellow" => ";43",
        "blue" => ";44",
        "magenta" => ";45",
        "cyan" => ";46",
        "lgray" => ";47"
    ];

    $textCode = $colors[$color] ?? $defaultColor;
    $bgCode = $bgColors[$bgColor] ?? '';

    echo "\e[".$textCode.$bgCode."m".$text."\e[0m";
}

// Color Echo with new Line
function cechoLn($color, $text){
    cecho ($color, $text."\n");
}

// Choose an option from menu
function choose($question, $options = [1,2,3]){
    cechoLn("white", $question);

    foreach($options as $i=>$opt)
    {
        cecho("yellow",$i.")");
        echo " ".$opt."\n";
    }

    $keys = array_keys($options);

    $fp = fopen('php://stdin', 'r'); // standart input 
    $passed = false;
    
    while (!$passed) {
        $line = fgets($fp, 1024); // get user input from stdin
        if (".\n" == $line)
        {
            exit;
        }
        else if(in_array($line, $keys) ) {
          $passed = true;
        } else {
          cecho("lRed","Incorrect entry! Try again. Use period (.) to exit.\n");
        }
    }

    return trim($line);
}

// User entry
function answer($question, $answer = ""){
    cechoLn("white", $question);

    if($answer)
    {
        echo "[";
        cecho("lMagenta", $answer);
        echo "] ";
    }

    $fp = fopen('php://stdin', 'r'); // standart input
    $line = trim(fgets($fp, 1024)); // get user input from stdin
    
    return strlen($line) ? $line : $answer;
}

echo "\n\n";
cecho("red","//");
echo " Kodventure ";
cechoLn("green", "WordPress Setup");
echo "-----------------------------------\n";

$urls = [
    1 => "https://tr.wordpress.org/latest-tr_TR.tar.gz",
    2 => "https://wordpress.org/latest.tar.gz"
];


$option = choose("Choose the language:", [1 =>"Türkçe", 2 => "English"]);
exec("wget ".$urls[$option]);

$file = basename($urls[$option]);

cecho("green","\nFile downloaded: ");
cechoLn("white", $file);
exec("tar -zxvf ".$file);
exec("rm ".$file); // remove zip file
cechoLn("green","\nFile extracted.");

$folder = answer("\nFolder name?", "wordpress");

exec("mv wordpress ".$folder); 

exec("chown -R www-data:www-data ".$folder); // nginx owner

$path = exec("pwd");

$confFolder = answer("\nNginx configuration folder: ", "/etc/nginx/conf.d/");
$confFile = answer("\nNginx configuration file: ", $folder.".conf");
$domains = answer("\nDomain and subdomains:", $folder.".com www.".$folder.".com");

$confText='server {
    listen   80;
    server_name '.$domains.';

    root '.$path."/".$folder.'/;
    index index.php index.html index.htm;

    location / {
         try_files $uri $uri/ /index.php$is_args$args;
    }

    location ~ \.php$ {
            try_files $uri /index.php =404;
    fastcgi_pass unix:/var/run/php/php7.4-fpm.sock;
            fastcgi_index index.php;
            fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
            include fastcgi_params;
    }
}';

file_put_contents($confFolder.$confFile, $confText);

cechoLn("green", "\nConfiguration file created: ".$confFolder.$confFile);

exec("service nginx restart");

cechoLn("green", "\nNginx restarted.");

echo "\n";
