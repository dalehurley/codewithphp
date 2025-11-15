# Chapter 28: Mocking & Interfaces

Learn mocking strategies using Go interfaces. Understand why Go's interfaces make testing easier than PHP.

## Files
1. 01-interface-mocking.go
2. 02-gomock.go
3. 03-testify-mock.go
4. 04-httptest-package.go

**Example**:
```go
type UserStore interface {
    GetUser(id int) (*User, error)
}

type MockUserStore struct{}

func (m *MockUserStore) GetUser(id int) (*User, error) {
    return &User{ID: id, Name: "Test"}, nil
}

func TestService(t *testing.T) {
    mockStore := &MockUserStore{}
    service := NewService(mockStore)
    // Test service with mock
}
```

**Key Takeaway**: Go's interfaces make mocking natural without complex frameworks.
