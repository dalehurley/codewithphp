import java.io.IOException;
import java.nio.file.Files;
import java.nio.file.Path;
import java.util.HashMap;
import java.util.Map;

public class OrderTotal {
    record Order(int id, int quantity, int unitCents) {
        Order {
            if (id < 1 || id > 9_999_999 || quantity < 1 || quantity > 1000 ||
                unitCents < 0 || unitCents > 1_000_000) {
                throw new IllegalArgumentException("Order fields are out of range.");
            }
        }

        int totalCents() {
            return quantity * unitCents;
        }
    }

    static int wholeNumber(String value) {
        if (!value.matches("0|[1-9][0-9]{0,6}")) {
            throw new IllegalArgumentException("Expected digits for a whole number.");
        }
        return Integer.parseInt(value);
    }

    static int fail(int status, String message) {
        System.err.println(message);
        return status;
    }

    public static void main(String[] args) {
        System.exit(run(args));
    }

    static int run(String[] args) {
        int id;
        try {
            id = args.length == 1 ? wholeNumber(args[0]) : 0;
            if (id == 0) {
                throw new IllegalArgumentException("Expected a positive order ID.");
            }
        } catch (IllegalArgumentException error) {
            return fail(2, "Enter one order ID from 1 to 9999999.");
        }

        String source;
        try {
            source = Files.readString(Path.of("orders.csv"));
        } catch (IOException error) {
            return fail(4, "Cannot read orders.csv. Run from the directory with the fixture.");
        }

        Map<Integer, Order> orders = new HashMap<>();
        String[] lines = source.replace("\r\n", "\n").split("\n", -1);
        for (int index = 0; index < lines.length; index++) {
            if (lines[index].isEmpty()) {
                continue;
            }
            try {
                String[] fields = lines[index].split(",", -1);
                if (fields.length != 3) {
                    throw new IllegalArgumentException("Expected three fields.");
                }
                var order = new Order(wholeNumber(fields[0]), wholeNumber(fields[1]), wholeNumber(fields[2]));
                if (orders.putIfAbsent(order.id(), order) != null) {
                    throw new IllegalArgumentException("Duplicate order ID.");
                }
            } catch (IllegalArgumentException error) {
                int lineNumber = index + 1;
                return fail(4, "Invalid orders.csv on line " + lineNumber + ". " + error.getMessage());
            }
        }

        Order order = orders.get(id);
        if (order == null) {
            return fail(3, "Order " + id + " was not found.");
        }

        System.out.println("Order " + id + ": " + order.totalCents() + " cents");
        return 0;
    }
}
