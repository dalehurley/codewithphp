# Chapter 26: Unit Testing

Master Go's built-in testing package. Learn table-driven tests, test coverage, and testing best practices - simpler than PHPUnit but just as powerful.

##Overview
Go's testing package is built into the standard library. Tests live alongside code, run with 'go test', and follow simple conventions. No complex setup like PHPUnit - just write functions starting with Test.

## Files
1. 01-test-basics.go - Writing tests, t.Error/t.Fatal
2. 02-table-driven-tests.go - Testing multiple inputs
3. 03-test-coverage.go - Coverage reports, benchmarks
4. 04-testify-package.go - Assertions with testify
5. 05-test-fixtures.go - Setup/teardown, test data
6. 06-integration-tests.go - Testing with real dependencies

## Quick Reference
**PHPUnit**:
```php
class UserTest extends TestCase {
    public function testCreateUser() {
        $user = new User('Alice');
        $this->assertEquals('Alice', $user->getName());
    }
}
```

**Go**:
```go
func TestCreateUser(t *testing.T) {
    user := NewUser("Alice")
    if user.Name != "Alice" {
        t.Errorf("got %s, want Alice", user.Name)
    }
}
```

## Best Practices
- Name test files *_test.go
- Use table-driven tests for multiple cases
- Keep tests simple and focused
- Use t.Helper() for test helpers
- Run tests with 'go test ./...'
- Aim for >80% coverage
- Mock external dependencies

**Key Takeaway**: Go's testing is simple and built-in. Write clear tests, use table-driven patterns, and leverage the tooling.
