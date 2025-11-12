<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

/*
|--------------------------------------------------------------------------
| Exercise 2: Calculator Controller
|--------------------------------------------------------------------------
|
| Step 1: Generate the controller
| php artisan make:controller CalculatorController
|
| Step 2: Add this code to the controller
| Step 3: Add route in routes/web.php:
| Route::get('/calculate/{num1}/{num2}/{operation}', [CalculatorController::class, 'calculate']);
*/

class CalculatorController extends Controller
{
    /**
     * Perform a calculation on two numbers
     *
     * @param float $num1
     * @param float $num2
     * @param string $operation
     * @return JsonResponse
     */
    public function calculate(float $num1, float $num2, string $operation): JsonResponse
    {
        // Validate operation
        $validOperations = ['add', 'subtract', 'multiply', 'divide'];

        if (!in_array($operation, $validOperations)) {
            return response()->json([
                'error' => 'Invalid operation',
                'valid_operations' => $validOperations
            ], 400);
        }

        // Perform calculation
        $result = match ($operation) {
            'add' => $num1 + $num2,
            'subtract' => $num1 - $num2,
            'multiply' => $num1 * $num2,
            'divide' => $this->divide($num1, $num2),
        };

        // Check for error (division by zero)
        if (is_array($result) && isset($result['error'])) {
            return response()->json($result, 400);
        }

        return response()->json([
            'num1' => $num1,
            'num2' => $num2,
            'operation' => $operation,
            'result' => $result
        ]);
    }

    /**
     * Safely divide two numbers
     *
     * @param float $num1
     * @param float $num2
     * @return float|array
     */
    private function divide(float $num1, float $num2): float|array
    {
        if ($num2 == 0) {
            return ['error' => 'Division by zero is not allowed'];
        }

        return $num1 / $num2;
    }
}

/*
|--------------------------------------------------------------------------
| Test examples:
|--------------------------------------------------------------------------
|
| http://localhost:8000/calculate/10/5/add
| Result: {"num1":10,"num2":5,"operation":"add","result":15}
|
| http://localhost:8000/calculate/10/5/multiply
| Result: {"num1":10,"num2":5,"operation":"multiply","result":50}
|
| http://localhost:8000/calculate/10/0/divide
| Result: {"error":"Division by zero is not allowed"}
|
| http://localhost:8000/calculate/10/5/invalid
| Result: {"error":"Invalid operation","valid_operations":["add","subtract","multiply","divide"]}
*/
