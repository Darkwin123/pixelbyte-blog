<?php
/**
 * Tag-related functions for PIXELBYTE Blog
 */

/**
 * Get all tags with post count
 * 
 * @param mysqli $conn Database connection
 * @return array Tags with post count
 */
function get_all_tags($conn) {
    $sql = "SELECT t.id, t.name, t.slug, COUNT(pt.post_id) as post_count 
            FROM blog_tags t
            LEFT JOIN post_tags pt ON t.id = pt.tag_id
            GROUP BY t.id
            ORDER BY t.name ASC";
            
    $result = $conn->query($sql);
    $tags = [];
    
    if ($result->num_rows > 0) {
        while($row = $result->fetch_assoc()) {
            $tags[] = $row;
        }
    }
    
    return $tags;
}

/**
 * Get tags for a specific post
 * 
 * @param mysqli $conn Database connection
 * @param int $post_id Post ID
 * @return array Tags for the post
 */
function get_post_tags($conn, $post_id) {
    $sql = "SELECT t.id, t.name, t.slug 
            FROM blog_tags t
            JOIN post_tags pt ON t.id = pt.tag_id
            WHERE pt.post_id = ?
            ORDER BY t.name ASC";
            
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $post_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $tags = [];
    
    if ($result->num_rows > 0) {
        while($row = $result->fetch_assoc()) {
            $tags[] = $row;
        }
    }
    
    return $tags;
}

/**
 * Get posts by tag slug
 * 
 * @param mysqli $conn Database connection
 * @param string $tag_slug Tag slug
 * @return array Posts with the specified tag
 */
function get_posts_by_tag($conn, $tag_slug) {
    $sql = "SELECT p.* 
            FROM blog_posts p
            JOIN post_tags pt ON p.id = pt.post_id
            JOIN blog_tags t ON pt.tag_id = t.id
            WHERE t.slug = ?
            ORDER BY p.created_at DESC";
            
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $tag_slug);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $posts = [];
    
    if ($result->num_rows > 0) {
        while($row = $result->fetch_assoc()) {
            // Create slug if it doesn't exist (for older posts)
            if (empty($row['slug'])) {
                // Create a URL-friendly slug from the title
                $row['slug'] = createSlugFromTitle($row['title'], $row['id']);
                
                // Update the database with the newly created slug
                $update_sql = "UPDATE blog_posts SET slug = ? WHERE id = ?";
                $update_stmt = $conn->prepare($update_sql);
                $update_stmt->bind_param("si", $row['slug'], $row['id']);
                $update_stmt->execute();
            }
            $posts[] = $row;
        }
    }
    
    return $posts;
}

/**
 * Helper function to create a slug from title
 *
 * @param string $title The post title
 * @param int $id The post ID
 * @return string The slug
 */
function createSlugFromTitle($title, $id) {
    // Convert to lowercase
    $slug = strtolower($title);
    
    // Replace non-alphanumeric characters with hyphens
    $slug = preg_replace('/[^a-z0-9]+/', '-', $slug);
    
    // Remove hyphens from beginning and end
    $slug = trim($slug, '-');
    
    // Ensure uniqueness by appending the ID
    $slug = $slug . '-' . $id;
    
    return $slug;
}    


/**
 * Save tags for a post
 * 
 * @param mysqli $conn Database connection
 * @param int $post_id Post ID
 * @param array $tag_ids Array of tag IDs
 * @return bool Success status
 */
function save_post_tags($conn, $post_id, $tag_ids) {
    // First, delete existing tag relationships
    $delete_sql = "DELETE FROM post_tags WHERE post_id = ?";
    $delete_stmt = $conn->prepare($delete_sql);
    $delete_stmt->bind_param("i", $post_id);
    $delete_stmt->execute();
    
    // Then insert new relationships
    if (!empty($tag_ids)) {
        $insert_sql = "INSERT INTO post_tags (post_id, tag_id) VALUES (?, ?)";
        $insert_stmt = $conn->prepare($insert_sql);
        
        foreach ($tag_ids as $tag_id) {
            $insert_stmt->bind_param("ii", $post_id, $tag_id);
            $insert_stmt->execute();
        }
    }
    
    return true;
}

/**
 * Create a new tag
 * 
 * @param mysqli $conn Database connection
 * @param string $name Tag name
 * @return int|bool New tag ID or false on failure
 */
function create_tag($conn, $name) {
    // Create slug from name
    $slug = strtolower(trim(preg_replace('/[^a-zA-Z0-9]+/', '-', $name), '-'));
    
    // Check if tag already exists
    $check_sql = "SELECT id FROM blog_tags WHERE slug = ?";
    $check_stmt = $conn->prepare($check_sql);
    $check_stmt->bind_param("s", $slug);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result();
    
    if ($check_result->num_rows > 0) {
        $row = $check_result->fetch_assoc();
        return $row['id']; // Return existing tag ID
    }
    
    // Create new tag
    $sql = "INSERT INTO blog_tags (name, slug) VALUES (?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $name, $slug);
    
    if ($stmt->execute()) {
        return $stmt->insert_id;
    }
    
    return false;
}

/**
 * Process tag input from form
 * 
 * @param mysqli $conn Database connection
 * @param string $tags_input Comma-separated tag names
 * @return array Tag IDs
 */
function process_tags_input($conn, $tags_input) {
    $tag_names = array_map('trim', explode(',', $tags_input));
    $tag_ids = [];
    
    foreach ($tag_names as $name) {
        if (!empty($name)) {
            $tag_id = create_tag($conn, $name);
            if ($tag_id) {
                $tag_ids[] = $tag_id;
            }
        }
    }
    
    return $tag_ids;
}
?>