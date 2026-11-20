# PHP Basics offline exercises

Open this folder in your terminal. You need PHP 8.0 or later to run these four exercises. Check it with `php -v`. The exercises use the PHP command line and work offline. They need no separate server or extensions.

For each exercise, edit the file under `starters/`. Compare the values printed by your program with the checks below. When you're ready, run the file with the same name under `solutions/`. The solutions may format their output differently from yours.

## 1. Simple calculations

Run `php starters/01-calculations.php`. Using the two supplied numbers, print their sum, difference, product and quotient. For `20` and `5`, look for `25`, `15`, `100` and `4`.

## 2. FizzBuzz

Run `php starters/03-fizzbuzz.php`. Loop from 1 to 100. Use the word Fizz for multiples of 3, Buzz for multiples of 5, and FizzBuzz for multiples of both. Print other numbers as they are. Check positions 1, 3, 5 and 15. The output should contain 100 items and end in Buzz.

The chapter solution first uses an `if` chain for 100 items and then demonstrates `match` for 50. The separate `03-fizzbuzz-match.php` solution uses `match` for all 100. The `if` chain makes it easy to add an action inside a branch. Use `match` when each branch only needs to choose a value. In both cases, test divisibility by 3 and 5 before either factor alone.

## 3. Temperature converter

Run `php starters/04-temperature.php`. Define the four functions in the comments, then call them with the supplied numbers. Check that 25 °C gives 77 °F and 298.15 K, 77 °F gives 25 °C, and 298.15 K gives 25 °C. Round only when displaying the answer.

## 4. Student grade report

Run `php starters/06-student-grades.php`. Load the supplied grade data. Calculate each student's average, print their highest and lowest grades, and order the students by average from highest to lowest. Check that Charlie is first with a 95.00 average, Alice's highest grade is 95, and Eve is last with an 80.40 average.

The chapter solution builds the average for each student with `foreach`. The `06-student-grades-array-map.php` solution uses `array_map` for that calculation. Prefer the loop when you need to examine each name and its grades or add per-student checks. The map expresses one operation on each grade list, while keeping the original keys because it receives one input array.

## Try the lessons

- [Write and Run Your First PHP Script](https://codewithphp.com/series/php-basics/chapters/01-your-first-php-script/)
- [Control Structures](https://codewithphp.com/series/php-basics/chapters/03-control-structures/)
- [Functions](https://codewithphp.com/series/php-basics/chapters/04-understanding-and-using-functions/)
- [Arrays](https://codewithphp.com/series/php-basics/chapters/06-deep-dive-into-arrays/)
