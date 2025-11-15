package main

import (
	"fmt"
	"strings"
	"unicode"
	"unicode/utf8"
)

/*
Strings and Runes in Go for PHP Developers
===========================================

PHP strings are mutable byte sequences:
	$str = "hello";
	$str[0] = "H";  // "Hello"
	$str .= " world";  // Concatenation

Go strings are immutable UTF-8 byte sequences:
	str := "hello"
	str[0] = "H"  // COMPILE ERROR! Strings are immutable
	str = str + " world"  // Creates new string

This chapter covers string handling, Unicode, and the rune type.
*/

func main() {
	fmt.Println("=== Strings and Runes for PHP Developers ===\n")

	demonstrateStringBasics()
	demonstrateStringImmutability()
	demonstrateStringIteration()
	demonstrateRunesAndUnicode()
	demonstrateStringOperations()
	demonstrateStringConversion()
	demonstrateStringBuilding()
	demonstrateCommonStringFunctions()
	demonstrateUnicodeHandling()
}

// demonstrateStringBasics shows basic string concepts
func demonstrateStringBasics() {
	fmt.Println("--- String Basics ---")

	// String literals - similar to PHP
	var simple string = "Hello, World!"
	var multiline string = `This is a
multiline string
in Go using backticks`

	fmt.Printf("Simple string: %s\n", simple)
	fmt.Printf("Multiline string:\n%s\n\n", multiline)

	// String length - IMPORTANT difference from PHP!
	// PHP strlen() counts characters
	// Go len() counts BYTES (not characters!)

	ascii := "hello"
	emoji := "hello😀"
	chinese := "你好世界"

	fmt.Printf("ASCII string: %q\n", ascii)
	fmt.Printf("  len() = %d bytes\n", len(ascii))
	fmt.Printf("  Characters: %d\n", utf8.RuneCountInString(ascii))

	fmt.Printf("\nString with emoji: %q\n", emoji)
	fmt.Printf("  len() = %d bytes (NOT 6!)\n", len(emoji))
	fmt.Printf("  Characters: %d\n", utf8.RuneCountInString(emoji))

	fmt.Printf("\nChinese string: %q\n", chinese)
	fmt.Printf("  len() = %d bytes\n", len(chinese))
	fmt.Printf("  Characters: %d\n", utf8.RuneCountInString(chinese))

	// Raw string literals (backticks)
	// No escape sequences processed
	raw := `Line 1\nLine 2\tTab`
	normal := "Line 1\nLine 2\tTab"

	fmt.Printf("\nRaw string: %s\n", raw)
	fmt.Printf("Normal string: %s\n", normal)

	fmt.Println()
}

// demonstrateStringImmutability shows Go strings are immutable
func demonstrateStringImmutability() {
	fmt.Println("--- String Immutability ---")

	// PHP: Strings are mutable
	// $str = "hello";
	// $str[0] = "H";  // OK - now "Hello"

	// Go: Strings are immutable
	str := "hello"
	fmt.Printf("Original: %s\n", str)

	// str[0] = 'H'  // COMPILE ERROR! Cannot modify string

	// Must create new string
	modified := "H" + str[1:]
	fmt.Printf("Modified: %s (new string created)\n", modified)
	fmt.Printf("Original unchanged: %s\n", str)

	// This is different from slices which ARE mutable
	bytes := []byte(str)  // Convert to mutable byte slice
	bytes[0] = 'H'
	modified2 := string(bytes)
	fmt.Printf("Via byte slice: %s\n", modified2)

	// Why immutable?
	// 1. Thread-safe (can share between goroutines)
	// 2. Can be used as map keys safely
	// 3. Better performance (can share underlying data)
	// 4. Prevents accidental modifications

	fmt.Println()
}

// demonstrateStringIteration shows different ways to iterate strings
func demonstrateStringIteration() {
	fmt.Println("--- String Iteration ---")

	str := "Go语言"  // "Go language" with Chinese characters

	// PHP: Simple iteration
	// for ($i = 0; $i < strlen($str); $i++) {
	//     echo $str[$i];
	// }

	// Go: WRONG way - iterates bytes, not characters!
	fmt.Println("Byte iteration (WRONG for Unicode):")
	for i := 0; i < len(str); i++ {
		fmt.Printf("  [%d] = %d (%c)\n", i, str[i], str[i])
	}

	// Go: RIGHT way - range iterates Unicode code points (runes)
	fmt.Println("\nRune iteration (CORRECT):")
	for i, r := range str {
		fmt.Printf("  Position %d: %c (rune: %U)\n", i, r, r)
	}

	// Just iterate runes without position
	fmt.Println("\nJust the characters:")
	for _, r := range str {
		fmt.Printf("%c ", r)
	}
	fmt.Println()

	// Convert to rune slice for random access
	runes := []rune(str)
	fmt.Printf("\nRune slice: %v\n", runes)
	fmt.Printf("First character: %c\n", runes[0])
	fmt.Printf("Third character: %c\n", runes[2])

	fmt.Println()
}

// demonstrateRunesAndUnicode explains runes
func demonstrateRunesAndUnicode() {
	fmt.Println("--- Runes and Unicode ---")

	// Rune is an alias for int32
	// Represents a Unicode code point

	// PHP: No rune concept, strings are byte sequences
	// Go: Runes for proper Unicode support

	var r1 rune = 'A'
	var r2 rune = '世'
	var r3 rune = '😀'

	fmt.Printf("Rune 'A': value=%d, hex=0x%X, char=%c\n", r1, r1, r1)
	fmt.Printf("Rune '世': value=%d, hex=0x%X, char=%c\n", r2, r2, r2)
	fmt.Printf("Rune '😀': value=%d, hex=0x%X, char=%c\n", r3, r3, r3)

	// Single quotes for runes, double quotes for strings
	var char rune = 'A'      // rune (int32)
	var str string = "A"     // string

	fmt.Printf("\n'A' is type: %T, value: %d\n", char, char)
	fmt.Printf("\"A\" is type: %T, value: %s\n", str, str)

	// Unicode escape sequences
	str = "\u4E16"  // 世
	fmt.Printf("Unicode escape \\u4E16 = %s\n", str)

	str = "\U0001F600"  // 😀
	fmt.Printf("Unicode escape \\U0001F600 = %s\n", str)

	// Check if valid UTF-8
	valid := "hello 世界"
	invalid := string([]byte{0xff, 0xfe, 0xfd})

	fmt.Printf("\n%q is valid UTF-8: %v\n", valid, utf8.ValidString(valid))
	fmt.Printf("%q is valid UTF-8: %v\n", invalid, utf8.ValidString(invalid))

	fmt.Println()
}

// demonstrateStringOperations shows common string operations
func demonstrateStringOperations() {
	fmt.Println("--- String Operations ---")

	// Concatenation
	// PHP: $result = $a . $b;
	// Go: result := a + b

	first := "Hello"
	last := "World"
	full := first + " " + last
	fmt.Printf("Concatenation: %s + %s = %s\n", first, last, full)

	// String repetition
	// PHP: str_repeat("abc", 3)
	// Go: strings.Repeat("abc", 3)
	repeated := strings.Repeat("Go! ", 3)
	fmt.Printf("Repeat: %s\n", repeated)

	// Substring
	// PHP: substr($str, $start, $length)
	// Go: str[start:end] (NOT length!)

	str := "Hello, World!"
	fmt.Printf("\nOriginal: %s\n", str)
	fmt.Printf("str[0:5]: %s\n", str[0:5])      // First 5 bytes
	fmt.Printf("str[7:]: %s\n", str[7:])        // From index 7 to end
	fmt.Printf("str[:5]: %s\n", str[:5])        // First 5 bytes
	fmt.Printf("str[7:12]: %s\n", str[7:12])    // Characters 7-11

	// Be careful with Unicode!
	unicode := "你好世界"
	// unicode[:1]  // Would give invalid UTF-8! (cuts in middle of character)
	runes := []rune(unicode)
	fmt.Printf("Safe Unicode substring: %s\n", string(runes[:2]))

	fmt.Println()
}

// demonstrateStringConversion shows string/[]byte/[]rune conversions
func demonstrateStringConversion() {
	fmt.Println("--- String Conversions ---")

	str := "Hello, 世界!"

	// String to []byte - gets UTF-8 bytes
	bytes := []byte(str)
	fmt.Printf("String to []byte: %v\n", bytes)
	fmt.Printf("  Length: %d bytes\n", len(bytes))

	// String to []rune - gets Unicode code points
	runes := []rune(str)
	fmt.Printf("String to []rune: %v\n", runes)
	fmt.Printf("  Length: %d runes\n", len(runes))

	// []byte to string
	backToStr := string(bytes)
	fmt.Printf("[]byte to string: %s\n", backToStr)

	// []rune to string
	backFromRunes := string(runes)
	fmt.Printf("[]rune to string: %s\n", backFromRunes)

	// Modify via byte slice
	bytes[0] = 'h'
	fmt.Printf("Modified via bytes: %s\n", string(bytes))

	// Modify via rune slice
	runes[7] = '界'
	fmt.Printf("Modified via runes: %s\n", string(runes))

	fmt.Println()
}

// demonstrateStringBuilding shows efficient string building
func demonstrateStringBuilding() {
	fmt.Println("--- Efficient String Building ---")

	// INEFFICIENT (like PHP string concatenation in loops)
	// Each += creates a new string
	result := ""
	for i := 0; i < 5; i++ {
		result += fmt.Sprintf("Line %d\n", i)
	}
	fmt.Println("Inefficient concatenation:")
	fmt.Print(result)

	// EFFICIENT - use strings.Builder (like PHP's implode or array join)
	// Similar to StringBuilder in other languages
	var builder strings.Builder

	for i := 0; i < 5; i++ {
		builder.WriteString(fmt.Sprintf("Line %d\n", i))
	}

	fmt.Println("Efficient strings.Builder:")
	fmt.Print(builder.String())

	// Builder methods
	var b strings.Builder
	b.WriteString("Hello")
	b.WriteString(" ")
	b.WriteString("World")
	b.WriteByte('!')
	b.WriteRune('😀')

	fmt.Printf("Built string: %s\n", b.String())
	fmt.Printf("Length: %d bytes\n", b.Len())

	// Reset and reuse
	b.Reset()
	b.WriteString("Reused builder")
	fmt.Printf("After reset: %s\n", b.String())

	fmt.Println()
}

// demonstrateCommonStringFunctions shows strings package functions
func demonstrateCommonStringFunctions() {
	fmt.Println("--- Common String Functions ---")

	str := "  Hello, Go World!  "

	// Trimming - similar to PHP trim()
	fmt.Printf("Original: %q\n", str)
	fmt.Printf("Trim: %q\n", strings.TrimSpace(str))
	fmt.Printf("TrimLeft: %q\n", strings.TrimLeft(str, " "))
	fmt.Printf("TrimRight: %q\n", strings.TrimRight(str, " "))
	fmt.Printf("Trim chars: %q\n", strings.Trim("!!!Hello!!!", "!"))

	// Case conversion
	fmt.Printf("\nToUpper: %s\n", strings.ToUpper(str))
	fmt.Printf("ToLower: %s\n", strings.ToLower(str))
	fmt.Printf("Title: %s\n", strings.Title(strings.ToLower(str)))

	// Searching
	fmt.Printf("\nContains 'Go': %v\n", strings.Contains(str, "Go"))
	fmt.Printf("HasPrefix '  He': %v\n", strings.HasPrefix(str, "  He"))
	fmt.Printf("HasSuffix '!  ': %v\n", strings.HasSuffix(str, "!  "))
	fmt.Printf("Index of 'Go': %d\n", strings.Index(str, "Go"))
	fmt.Printf("LastIndex of 'o': %d\n", strings.LastIndex(str, "o"))
	fmt.Printf("Count 'o': %d\n", strings.Count(str, "o"))

	// Splitting and joining
	csv := "apple,banana,orange,grape"
	parts := strings.Split(csv, ",")
	fmt.Printf("\nSplit: %v\n", parts)

	joined := strings.Join(parts, " | ")
	fmt.Printf("Join: %s\n", joined)

	// Replacing
	text := "foo foo foo"
	fmt.Printf("\nReplace (n=2): %s\n", strings.Replace(text, "foo", "bar", 2))
	fmt.Printf("ReplaceAll: %s\n", strings.ReplaceAll(text, "foo", "bar"))

	// Fields (split on whitespace)
	spaced := "  one   two  three   "
	fields := strings.Fields(spaced)
	fmt.Printf("\nFields: %v\n", fields)

	fmt.Println()
}

// demonstrateUnicodeHandling shows Unicode-specific operations
func demonstrateUnicodeHandling() {
	fmt.Println("--- Unicode Handling ---")

	text := "Hello, 世界! 😀"

	fmt.Printf("Text: %s\n", text)
	fmt.Printf("Byte length: %d\n", len(text))
	fmt.Printf("Rune count: %d\n", utf8.RuneCountInString(text))

	// Iterate and classify characters
	fmt.Println("\nCharacter classification:")
	for _, r := range text {
		fmt.Printf("  %c - ", r)

		if unicode.IsLetter(r) {
			fmt.Print("Letter ")
		}
		if unicode.IsDigit(r) {
			fmt.Print("Digit ")
		}
		if unicode.IsSpace(r) {
			fmt.Print("Space ")
		}
		if unicode.IsPunct(r) {
			fmt.Print("Punctuation ")
		}
		if unicode.IsSymbol(r) {
			fmt.Print("Symbol ")
		}

		fmt.Println()
	}

	// Case conversion for runes
	fmt.Println("\nCase conversion:")
	for _, r := range "Hello" {
		fmt.Printf("%c -> upper: %c, lower: %c\n",
			r, unicode.ToUpper(r), unicode.ToLower(r))
	}

	// Decode runes manually
	fmt.Println("\nManual UTF-8 decoding:")
	str := "Go语"
	for i := 0; i < len(str); {
		r, size := utf8.DecodeRuneInString(str[i:])
		fmt.Printf("Position %d: %c (size: %d bytes)\n", i, r, size)
		i += size
	}

	// Check specific Unicode categories
	fmt.Println("\nUnicode categories:")
	samples := []rune{'A', '5', ' ', '世', '😀', '+'}
	for _, r := range samples {
		fmt.Printf("%c: Letter=%v Digit=%v Space=%v Symbol=%v\n",
			r, unicode.IsLetter(r), unicode.IsDigit(r),
			unicode.IsSpace(r), unicode.IsSymbol(r))
	}

	fmt.Println()
}

/*
Expected Output:
=== Strings and Runes for PHP Developers ===

--- String Basics ---
Simple string: Hello, World!
Multiline string:
This is a
multiline string
in Go using backticks

ASCII string: "hello"
  len() = 5 bytes
  Characters: 5

String with emoji: "hello😀"
  len() = 9 bytes (NOT 6!)
  Characters: 6

Chinese string: "你好世界"
  len() = 12 bytes
  Characters: 4

Raw string: Line 1\nLine 2\tTab
Normal string: Line 1
Line 2	Tab

--- String Immutability ---
Original: hello
Modified: Hello (new string created)
Original unchanged: hello
Via byte slice: Hello

--- String Iteration ---
Byte iteration (WRONG for Unicode):
  [0] = 71 (G)
  [1] = 111 (o)
  [2] = 232 (è)
  [3] = 175 (¯)
  [4] = 173 (­)
  [5] = 232 (è)
  [6] = 168 (¨)
  [7] = 128 (€)

Rune iteration (CORRECT):
  Position 0: G (rune: U+0047)
  Position 1: o (rune: U+006F)
  Position 2: 语 (rune: U+8BED)
  Position 5: 言 (rune: U+8A00)

Just the characters:
G o 语 言

Rune slice: [71 111 35821 35328]
First character: G
Third character: 语

--- Runes and Unicode ---
Rune 'A': value=65, hex=0x41, char=A
Rune '世': value=19990, hex=0x4E16, char=世
Rune '😀': value=128512, hex=0x1F600, char=😀

'A' is type: int32, value: 65
"A" is type: string, value: A
Unicode escape \u4E16 = 世
Unicode escape \U0001F600 = 😀

"hello 世界" is valid UTF-8: true
"\xff\xfe\xfd" is valid UTF-8: false

--- String Operations ---
Concatenation: Hello + World = Hello World
Repeat: Go! Go! Go!

Original: Hello, World!
str[0:5]: Hello
str[7:]: World!
str[:5]: Hello
str[7:12]: World
Safe Unicode substring: 你好

--- String Conversions ---
String to []byte: [72 101 108 108 111 44 32 228 184 150 231 149 140 33]
  Length: 14 bytes
String to []rune: [72 101 108 108 111 44 32 19990 30028 33]
  Length: 10 runes
[]byte to string: Hello, 世界!
[]rune to string: Hello, 世界!
Modified via bytes: hello, 世界!
Modified via runes: Hello, 界界!

--- Efficient String Building ---
Inefficient concatenation:
Line 0
Line 1
Line 2
Line 3
Line 4

Efficient strings.Builder:
Line 0
Line 1
Line 2
Line 3
Line 4

Built string: Hello World!😀
Length: 16 bytes
After reset: Reused builder

--- Common String Functions ---
Original: "  Hello, Go World!  "
Trim: "Hello, Go World!"
TrimLeft: "Hello, Go World!  "
TrimRight: "  Hello, Go World!"
Trim chars: "Hello"

ToUpper:   HELLO, GO WORLD!
ToLower:   hello, go world!
Title:   Hello, Go World!

Contains 'Go': true
HasPrefix '  He': true
HasSuffix '!  ': true
Index of 'Go': 10
LastIndex of 'o': 15
Count 'o': 3

Split: [apple banana orange grape]
Join: apple | banana | orange | grape

Replace (n=2): bar bar foo
ReplaceAll: bar bar bar

Fields: [one two three]

--- Unicode Handling ---
Text: Hello, 世界! 😀
Byte length: 18
Rune count: 11

Character classification:
  H - Letter
  e - Letter
  l - Letter
  l - Letter
  o - Letter
  , - Punctuation
    - Space
  世 - Letter
  界 - Letter
  ! - Punctuation
    - Space
  😀 - Symbol

Case conversion:
H -> upper: H, lower: h
e -> upper: E, lower: e
l -> upper: L, lower: l
l -> upper: L, lower: l
o -> upper: O, lower: o

Manual UTF-8 decoding:
Position 0: G (size: 1 bytes)
Position 1: o (size: 1 bytes)
Position 2: 语 (size: 3 bytes)

Unicode categories:
A: Letter=true Digit=false Space=false Symbol=false
5: Letter=false Digit=true Space=false Symbol=false
 : Letter=false Digit=false Space=true Symbol=false
世: Letter=true Digit=false Space=false Symbol=false
😀: Letter=false Digit=false Space=false Symbol=true
+: Letter=false Digit=false Space=false Symbol=true
*/
