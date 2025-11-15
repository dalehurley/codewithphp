package main

import "fmt"

/*
PHP VERSION:
<?php
echo "Hello, World!\n";

KEY DIFFERENCES:
1. Go requires package declaration (every file belongs to a package)
2. Go requires explicit imports (no auto-loading)
3. Go programs need a main() function in package main
4. Go is compiled before running (php -> go build -> binary)
5. No <?php tags needed in Go
*/

func main() {
	// Simple output - like PHP's echo
	fmt.Println("Hello, World!")

	// Multiple ways to print in Go
	fmt.Print("Hello ")
	fmt.Print("from ")
	fmt.Println("Go!")

	// Formatted printing (like sprintf)
	name := "PHP Developer"
	fmt.Printf("Welcome, %s!\n", name)

	// String formatting and assignment
	greeting := fmt.Sprintf("Hello, %s. Welcome to Go!", name)
	fmt.Println(greeting)

	/*
	COMPILATION COMPARISON:

	PHP (interpreted):
	  $ php hello.php
	  Output: Hello, World!

	Go (compiled):
	  $ go build hello.go     # Creates binary
	  $ ./hello               # Run binary
	  Output: Hello, World!

	Go (run without saving binary):
	  $ go run hello.go
	  Output: Hello, World!
	*/

	fmt.Println("\n=== Compilation Info ===")
	fmt.Println("This Go program was compiled before running!")
	fmt.Println("Binary size: ~2MB (includes runtime)")
	fmt.Println("Startup time: ~5ms (vs PHP: ~50ms)")
	fmt.Println("No PHP runtime needed to deploy!")
}

/*
OUTPUT:
Hello, World!
Hello from Go!
Welcome, PHP Developer!
Hello, PHP Developer. Welcome to Go!

=== Compilation Info ===
This Go program was compiled before running!
Binary size: ~2MB (includes runtime)
Startup time: ~5ms (vs PHP: ~50ms)
No PHP runtime needed to deploy!
*/

/*
KEY TAKEAWAYS FOR PHP DEVELOPERS:

1. Every Go file starts with: package <name>
2. Executable programs use: package main
3. Entry point is: func main()
4. Import standard library packages: import "fmt"
5. Variables declared with type or inferred with :=
6. Go is compiled - binaries are fast and standalone
7. No semicolons needed (usually - compiler adds them)
8. Exported names start with capital letters

NEXT STEPS:
- Run: go run 01-hello-from-php-to-go.go
- Build: go build 01-hello-from-php-to-go.go
- See how fast the compiled binary starts!
*/
