<?php
require_once 'config.php';

/**
 * Get all products with optional pagination
 *
 * @param mysqli $conn Database connection
 * @param int $page Current page number
 * @param int $per_page Products per page
 * @param string $order_by Order by field
 * @return array Array with products and pagination data
 */
function getProducts($conn, $page = 1, $per_page = PRODUCTS_PER_PAGE, $order_by = 'created_at DESC') {
    $offset = ($page - 1) * $per_page;
    
    // Count total products
    $count_sql = "SELECT COUNT(*) as total FROM store_products";
    $count_result = $conn->query($count_sql);
    $total_products = $count_result->fetch_assoc()['total'];
    $total_pages = ceil($total_products / $per_page);
    
    // Get products
    $sql = "SELECT * FROM store_products ORDER BY $order_by LIMIT ?, ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $offset, $per_page);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $products = [];
    while($product = $result->fetch_assoc()) {
        $products[] = $product;
    }
    
    return [
        'products' => $products,
        'pagination' => [
            'current_page' => $page,
            'total_pages' => $total_pages,
            'total_products' => $total_products
        ]
    ];
}

/**
 * Get products by category
 *
 * @param mysqli $conn Database connection
 * @param string $category Category name or slug
 * @param int $page Current page number
 * @param int $per_page Products per page
 * @return array Array with products and pagination data
 */
function getProductsByCategory($conn, $category, $page = 1, $per_page = PRODUCTS_PER_PAGE) {
    $offset = ($page - 1) * $per_page;
    
    // Determine if $category is a slug or name
    $is_slug = strpos($category, '-') !== false;
    
    if ($is_slug) {
        // Get category by slug
        $cat_sql = "SELECT name FROM store_categories WHERE slug = ?";
        $cat_stmt = $conn->prepare($cat_sql);
        $cat_stmt->bind_param("s", $category);
        $cat_stmt->execute();
        $cat_result = $cat_stmt->get_result();
        
        if ($cat_result->num_rows == 0) {
            return [
                'products' => [],
                'pagination' => [
                    'current_page' => $page,
                    'total_pages' => 0,
                    'total_products' => 0
                ]
            ];
        }
        
        $category = $cat_result->fetch_assoc()['name'];
    }
    
    // Count total products in category
    $count_sql = "SELECT COUNT(*) as total FROM store_products WHERE category = ?";
    $count_stmt = $conn->prepare($count_sql);
    $count_stmt->bind_param("s", $category);
    $count_stmt->execute();
    $count_result = $count_stmt->get_result();
    $total_products = $count_result->fetch_assoc()['total'];
    $total_pages = ceil($total_products / $per_page);
    
    // Get products in category
    $sql = "SELECT * FROM store_products WHERE category = ? ORDER BY created_at DESC LIMIT ?, ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sii", $category, $offset, $per_page);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $products = [];
    while($product = $result->fetch_assoc()) {
        $products[] = $product;
    }
    
    return [
        'products' => $products,
        'pagination' => [
            'current_page' => $page,
            'total_pages' => $total_pages,
            'total_products' => $total_products
        ]
    ];
}

/**
 * Get product by ID or slug
 *
 * @param mysqli $conn Database connection
 * @param mixed $identifier Product ID or slug
 * @param bool $is_slug Whether the identifier is a slug
 * @return array|false Product data or false if not found
 */
function getProduct($conn, $identifier, $is_slug = false) {
    $sql = $is_slug ? "SELECT * FROM store_products WHERE slug = ?" : "SELECT * FROM store_products WHERE id = ?";
    $stmt = $conn->prepare($sql);
    
    if ($is_slug) {
        $stmt->bind_param("s", $identifier);
    } else {
        $stmt->bind_param("i", $identifier);
    }
    
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows == 0) {
        return false;
    }
    
    return $result->fetch_assoc();
}

/**
 * Get featured products
 *
 * @param mysqli $conn Database connection
 * @param int $limit Number of products to get
 * @return array Array of featured products
 */
function getFeaturedProducts($conn, $limit = 4) {
    $sql = "SELECT * FROM store_products WHERE is_featured = 1 ORDER BY created_at DESC LIMIT ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $limit);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $products = [];
    while($product = $result->fetch_assoc()) {
        $products[] = $product;
    }
    
    return $products;
}

/**
 * Get related products
 *
 * @param mysqli $conn Database connection
 * @param int $product_id Product ID to find related products for
 * @param int $limit Number of related products to get
 * @return array Array of related products
 */
function getRelatedProducts($conn, $product_id, $limit = 4) {
    // Get product category
    $cat_sql = "SELECT category FROM store_products WHERE id = ?";
    $cat_stmt = $conn->prepare($cat_sql);
    $cat_stmt->bind_param("i", $product_id);
    $cat_stmt->execute();
    $cat_result = $cat_stmt->get_result();
    
    if ($cat_result->num_rows == 0) {
        return [];
    }
    
    $category = $cat_result->fetch_assoc()['category'];
    
    // Get related products in same category
    $sql = "SELECT * FROM store_products WHERE category = ? AND id != ? ORDER BY RAND() LIMIT ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sii", $category, $product_id, $limit);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $products = [];
    while($product = $result->fetch_assoc()) {
        $products[] = $product;
    }
    
    return $products;
}

/**
 * Search products
 *
 * @param mysqli $conn Database connection
 * @param string $query Search query
 * @param int $page Current page number
 * @param int $per_page Products per page
 * @return array Array with products and pagination data
 */
function searchProducts($conn, $query, $page = 1, $per_page = PRODUCTS_PER_PAGE) {
    $offset = ($page - 1) * $per_page;
    $search_term = '%' . $query . '%';
    
    // Count total matches
    $count_sql = "SELECT COUNT(*) as total FROM store_products 
                  WHERE title LIKE ? OR description LIKE ? OR category LIKE ?";
    $count_stmt = $conn->prepare($count_sql);
    $count_stmt->bind_param("sss", $search_term, $search_term, $search_term);
    $count_stmt->execute();
    $count_result = $count_stmt->get_result();
    $total_products = $count_result->fetch_assoc()['total'];
    $total_pages = ceil($total_products / $per_page);
    
    // Get matching products
    $sql = "SELECT * FROM store_products 
            WHERE title LIKE ? OR description LIKE ? OR category LIKE ? 
            ORDER BY created_at DESC LIMIT ?, ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssii", $search_term, $search_term, $search_term, $offset, $per_page);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $products = [];
    while($product = $result->fetch_assoc()) {
        $products[] = $product;
    }
    
    return [
        'products' => $products,
        'pagination' => [
            'current_page' => $page,
            'total_pages' => $total_pages,
            'total_products' => $total_products
        ]
    ];
}

/**
 * Get all categories
 *
 * @param mysqli $conn Database connection
 * @return array Array of categories with product counts
 */
function getCategories($conn) {
    $sql = "SELECT c.id, c.name, c.slug, c.description, COUNT(p.id) as product_count 
            FROM store_categories c
            LEFT JOIN store_products p ON c.name = p.category
            GROUP BY c.id
            ORDER BY c.name";
    $result = $conn->query($sql);
    
    $categories = [];
    while($category = $result->fetch_assoc()) {
        $categories[] = $category;
    }
    
    return $categories;
}

/**
 * Get category by slug
 *
 * @param mysqli $conn Database connection
 * @param string $slug Category slug
 * @return array|false Category data or false if not found
 */
function getCategoryBySlug($conn, $slug) {
    $sql = "SELECT * FROM store_categories WHERE slug = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $slug);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows == 0) {
        return false;
    }
    
    return $result->fetch_assoc();
}