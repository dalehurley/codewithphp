<?php

declare(strict_types=1);

/**
 * Serialize and Deserialize Binary Tree
 *
 * Demonstrates:
 * - Converting tree to string representation
 * - Reconstructing tree from string
 * - Multiple serialization formats
 */

require_once '01-tree-node-basics.php';
require_once '02-tree-traversals.php';

echo "=== Serialize and Deserialize Binary Tree ===\n\n";

class TreeSerializer
{
    /**
     * Serialize tree to string using preorder traversal
     * Format: "val,val,val" with "null" for empty nodes
     */
    public function serialize(?TreeNode $root): string
    {
        $parts = [];
        $this->serializeHelper($root, $parts);
        return implode(',', $parts);
    }

    private function serializeHelper(?TreeNode $node, array &$parts): void
    {
        if ($node === null) {
            $parts[] = 'null';
            return;
        }

        $parts[] = (string)$node->value;
        $this->serializeHelper($node->left, $parts);
        $this->serializeHelper($node->right, $parts);
    }

    /**
     * Deserialize string back to tree
     */
    public function deserialize(string $data): ?TreeNode
    {
        $parts = explode(',', $data);
        $index = 0;
        return $this->deserializeHelper($parts, $index);
    }

    private function deserializeHelper(array $parts, int &$index): ?TreeNode
    {
        if ($index >= count($parts) || $parts[$index] === 'null') {
            $index++;
            return null;
        }

        $node = new TreeNode((int)$parts[$index]);
        $index++;

        $node->left = $this->deserializeHelper($parts, $index);
        $node->right = $this->deserializeHelper($parts, $index);

        return $node;
    }

    /**
     * Serialize using level-order (BFS) - more compact
     */
    public function serializeLevelOrder(?TreeNode $root): string
    {
        if ($root === null) {
            return '';
        }

        $result = [];
        $queue = [$root];

        while (!empty($queue)) {
            $node = array_shift($queue);

            if ($node === null) {
                $result[] = 'null';
            } else {
                $result[] = (string)$node->value;
                $queue[] = $node->left;
                $queue[] = $node->right;
            }
        }

        // Remove trailing nulls
        while (end($result) === 'null') {
            array_pop($result);
        }

        return implode(',', $result);
    }

    /**
     * Deserialize from level-order
     */
    public function deserializeLevelOrder(string $data): ?TreeNode
    {
        if ($data === '') {
            return null;
        }

        $parts = explode(',', $data);
        $root = new TreeNode((int)$parts[0]);
        $queue = [$root];
        $index = 1;

        while (!empty($queue) && $index < count($parts)) {
            $node = array_shift($queue);

            // Left child
            if ($index < count($parts) && $parts[$index] !== 'null') {
                $node->left = new TreeNode((int)$parts[$index]);
                $queue[] = $node->left;
            }
            $index++;

            // Right child
            if ($index < count($parts) && $parts[$index] !== 'null') {
                $node->right = new TreeNode((int)$parts[$index]);
                $queue[] = $node->right;
            }
            $index++;
        }

        return $root;
    }
}

// Build test tree:
//        5
//       / \
//      3   7
//     / \   \
//    2   4   8

$originalTree = new TreeNode(5);
$originalTree->left = new TreeNode(3);
$originalTree->right = new TreeNode(7);
$originalTree->left->left = new TreeNode(2);
$originalTree->left->right = new TreeNode(4);
$originalTree->right->right = new TreeNode(8);

$serializer = new TreeSerializer();

echo "Original tree (inorder): " . json_encode(inorderTraversal($originalTree)) . "\n\n";

// Test preorder serialization
echo "1. Preorder Serialization\n";
$preorderStr = $serializer->serialize($originalTree);
echo "   Serialized: $preorderStr\n";

$deserializedTree = $serializer->deserialize($preorderStr);
echo "   Deserialized (inorder): " . json_encode(inorderTraversal($deserializedTree)) . "\n";
echo "   Match: " . (inorderTraversal($originalTree) === inorderTraversal($deserializedTree) ? "✓ Yes" : "✗ No") . "\n\n";

// Test level-order serialization
echo "2. Level-Order Serialization\n";
$levelOrderStr = $serializer->serializeLevelOrder($originalTree);
echo "   Serialized: $levelOrderStr\n";

$deserializedTree2 = $serializer->deserializeLevelOrder($levelOrderStr);
echo "   Deserialized (inorder): " . json_encode(inorderTraversal($deserializedTree2)) . "\n";
echo "   Match: " . (inorderTraversal($originalTree) === inorderTraversal($deserializedTree2) ? "✓ Yes" : "✗ No") . "\n\n";

// Test with null tree
echo "3. Edge Case: Null Tree\n";
$nullStr = $serializer->serialize(null);
echo "   Serialized null tree: '$nullStr'\n";
$nullTree = $serializer->deserialize($nullStr);
echo "   Deserialized: " . ($nullTree === null ? "null" : "not null") . "\n\n";

// Test with single node
echo "4. Edge Case: Single Node\n";
$singleNode = new TreeNode(42);
$singleStr = $serializer->serialize($singleNode);
echo "   Serialized: $singleStr\n";
$singleDeser = $serializer->deserialize($singleStr);
echo "   Deserialized value: " . $singleDeser->value . "\n\n";

// JSON format (alternative)
echo "5. Alternative: JSON Format\n";

function treeToJSON(?TreeNode $node): string
{
    if ($node === null) {
        return 'null';
    }

    return json_encode([
        'value' => $node->value,
        'left' => $node->left ? json_decode(treeToJSON($node->left), true) : null,
        'right' => $node->right ? json_decode(treeToJSON($node->right), true) : null
    ]);
}

$jsonStr = treeToJSON($originalTree);
echo "   JSON format:\n   " . json_encode(json_decode($jsonStr), JSON_PRETTY_PRINT) . "\n";

echo "\n✅ Complete! Serialization/deserialization demonstrated.\n";
