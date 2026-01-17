<?php

declare(strict_types=1);

namespace Tests\Chapter08;

use PHPUnit\Framework\TestCase;
use DataScience\ML\SimpleRegressor;

class SimpleRegressorTest extends TestCase
{
    private SimpleRegressor $regressor;

    protected function setUp(): void
    {
        $this->regressor = new SimpleRegressor();
    }

    public function test_perfect_linear_relationship(): void
    {
        // Perfect line: y = 2x + 3
        $x = [1, 2, 3, 4, 5];
        $y = [5, 7, 9, 11, 13];

        $this->regressor->train($x, $y);

        $params = $this->regressor->getParameters();

        $this->assertEqualsWithDelta(2.0, $params['slope'], 0.001);
        $this->assertEqualsWithDelta(3.0, $params['intercept'], 0.001);

        // R² should be 1.0 for perfect fit
        $rSquared = $this->regressor->rSquared($x, $y);
        $this->assertEqualsWithDelta(1.0, $rSquared, 0.001);
    }

    public function test_prediction_accuracy(): void
    {
        $x = [1, 2, 3, 4, 5];
        $y = [2, 4, 5, 4, 5];

        $this->regressor->train($x, $y);

        // Predict for x=3 (should be close to 5)
        $prediction = $this->regressor->predict(3.0);
        $this->assertEqualsWithDelta(4.0, $prediction, 1.0);
    }

    public function test_throws_exception_on_constant_x(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Cannot fit line');

        // All X values identical
        $x = [5, 5, 5, 5];
        $y = [1, 2, 3, 4];

        $this->regressor->train($x, $y);
    }

    public function test_mean_absolute_error_calculation(): void
    {
        $x = [1, 2, 3];
        $y = [1, 2, 3];

        $this->regressor->train($x, $y);

        $mae = $this->regressor->meanAbsoluteError($x, $y);

        // Perfect fit should have MAE = 0
        $this->assertEqualsWithDelta(0.0, $mae, 0.001);
    }

    public function test_root_mean_squared_error(): void
    {
        $x = [1, 2, 3];
        $y = [1, 2, 3];

        $this->regressor->train($x, $y);

        $rmse = $this->regressor->rootMeanSquaredError($x, $y);

        // Perfect fit should have RMSE = 0
        $this->assertEqualsWithDelta(0.0, $rmse, 0.001);
    }

    public function test_predict_batch(): void
    {
        $x = [1, 2, 3];
        $y = [2, 4, 6];

        $this->regressor->train($x, $y);

        $predictions = $this->regressor->predictBatch([1, 2, 3]);

        $this->assertCount(3, $predictions);
        $this->assertEqualsWithDelta(2, $predictions[0], 0.1);
        $this->assertEqualsWithDelta(4, $predictions[1], 0.1);
        $this->assertEqualsWithDelta(6, $predictions[2], 0.1);
    }

    public function test_throws_exception_when_not_trained(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Model not trained');

        $this->regressor->predict(5.0);
    }

    public function test_throws_exception_on_mismatched_arrays(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('X and Y must have same length');

        $x = [1, 2, 3];
        $y = [1, 2];

        $this->regressor->train($x, $y);
    }

    public function test_throws_exception_on_insufficient_data(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Need at least 2 data points');

        $x = [1];
        $y = [1];

        $this->regressor->train($x, $y);
    }

    public function test_save_and_load_model(): void
    {
        $x = [1, 2, 3];
        $y = [2, 4, 6];

        $this->regressor->train($x, $y);

        $filepath = sys_get_temp_dir() . '/test_regressor.json';

        // Save model
        $this->regressor->save($filepath);
        $this->assertFileExists($filepath);

        // Load model
        $loadedRegressor = SimpleRegressor::load($filepath);
        $this->assertInstanceOf(SimpleRegressor::class, $loadedRegressor);

        // Test prediction works on loaded model
        $prediction = $loadedRegressor->predict(2.0);
        $this->assertEqualsWithDelta(4.0, $prediction, 0.1);

        // Clean up
        unlink($filepath);
    }

    public function test_r_squared_with_poor_fit(): void
    {
        $x = [1, 2, 3, 4, 5];
        $y = [1, 1, 1, 10, 10];

        $this->regressor->train($x, $y);

        // Should trigger warning for negative R²
        $rSquared = @$this->regressor->rSquared($x, $y);

        // Poor fit can have low or negative R²
        $this->assertLessThan(0.5, $rSquared);
    }

    public function test_mean_absolute_percentage_error(): void
    {
        $x = [1, 2, 3];
        $y = [100, 200, 300];

        $this->regressor->train($x, $y);

        $mape = $this->regressor->meanAbsolutePercentageError($x, $y);

        // Perfect fit should have MAPE close to 0
        $this->assertLessThan(1.0, $mape);
    }

    public function test_throws_exception_on_mape_with_zero_values(): void
    {
        $x = [1, 2, 3];
        $y = [1, 0, 3];

        $this->regressor->train($x, $y);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Cannot calculate MAPE with zero actual values');

        $this->regressor->meanAbsolutePercentageError($x, $y);
    }
}
