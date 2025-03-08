<!-- Footer -->
    <footer>
        <div class="container">
            <div class="footer-content">
                <div>
                    <div class="footer-logo">PIXELBYTE</div>
                    <p>Creating unique digital experiences that break the conventional mold with modern templates and themes.</p>
                    <div class="social-links">
                        <a href="#" aria-label="Twitter">T</a>
                        <a href="#" aria-label="Instagram">I</a>
                        <a href="#" aria-label="Dribbble">D</a>
                        <a href="#" aria-label="GitHub">G</a>
                        <a href="#" aria-label="LinkedIn">L</a>
                    </div>
                </div>
                <div class="footer-nav">
                    <h3>Navigation</h3>
                    <ul>
                        <li><a href="<?php echo $site_root; ?>">Home</a></li>
                        <li><a href="<?php echo $store_root; ?>">Store</a></li>
                        <li><a href="<?php echo $blog_root; ?>">Blog</a></li>
                        <li><a href="<?php echo $site_root; ?>#features">Features</a></li>
                        <li><a href="<?php echo $site_root; ?>#contact">Contact</a></li>
                    </ul>
                </div>
                
                <?php if($is_blog): ?>
                <!-- Blog Categories -->
                <div class="footer-nav">
                    <h3>Categories</h3>
                    <ul>
                        <?php
                        // Get categories for footer
                        $footer_categories_sql = "SELECT category, COUNT(*) as count FROM blog_posts GROUP BY category ORDER BY count DESC LIMIT 5";
                        $footer_categories_result = $conn->query($footer_categories_sql);
                        
                        if ($footer_categories_result && $footer_categories_result->num_rows > 0):
                            while($category = $footer_categories_result->fetch_assoc()):
                        ?>
                            <li>
                                <a href="<?php echo $blog_root . 'category/' . urlencode($category['category']); ?>">
                                    <?php echo htmlspecialchars($category['category']); ?>
                                </a>
                            </li>
                        <?php 
                            endwhile;
                        else:
                        ?>
                            <li><a href="#">Web Design</a></li>
                            <li><a href="#">Development</a></li>
                            <li><a href="#">UX/UI</a></li>
                            <li><a href="#">Typography</a></li>
                            <li><a href="#">Inspiration</a></li>
                        <?php endif; ?>
                    </ul>
                </div>
                <?php elseif($is_store): ?>
                <!-- Store Categories -->
                <div class="footer-nav">
                    <h3>Categories</h3>
                    <ul>
                        <?php
                        // Get categories for footer
                        $footer_categories_sql = "SELECT c.name, c.slug FROM store_categories c 
                                                 LEFT JOIN store_products p ON c.name = p.category 
                                                 GROUP BY c.name 
                                                 ORDER BY COUNT(p.id) DESC LIMIT 5";
                        $footer_categories_result = $conn->query($footer_categories_sql);
                        
                        if ($footer_categories_result && $footer_categories_result->num_rows > 0):
                            while($category = $footer_categories_result->fetch_assoc()):
                        ?>
                            <li>
                                <a href="<?php echo $store_root . 'category/' . $category['slug']; ?>">
                                    <?php echo htmlspecialchars($category['name']); ?>
                                </a>
                            </li>
                        <?php 
                            endwhile;
                        else:
                        ?>
                            <li><a href="#">Portfolio</a></li>
                            <li><a href="#">E-commerce</a></li>
                            <li><a href="#">Blog</a></li>
                            <li><a href="#">Agency</a></li>
                            <li><a href="#">Business</a></li>
                        <?php endif; ?>
                    </ul>
                </div>
                <?php else: ?>
                <!-- Home Page Links -->
                <div class="footer-nav">
                    <h3>Our Work</h3>
                    <ul>
                        <li><a href="<?php echo $store_root; ?>">Templates</a></li>
                        <li><a href="#">Services</a></li>
                        <li><a href="#">Our Process</a></li>
                        <li><a href="#">Testimonials</a></li>
                        <li><a href="#">About Us</a></li>
                    </ul>
                </div>
                <?php endif; ?>
            </div>
            <div class="copyright">
                <p>&copy; <?php echo date('Y'); ?> PIXELBYTE. All rights reserved.</p>
            </div>
        </div>
    </footer>

    <!-- Main JS -->
    <script src="<?php echo $site_root; ?>assets/js/main.js"></script>
    
    <!-- Page-specific JS if needed -->
    <?php if(isset($page_js)): ?>
    <script src="<?php echo $page_js; ?>"></script>
    <?php endif; ?>
</body>
</html>