<!-- Footer -->
<footer class="footer">
    <div class="container">
        <div class="footer-content">
            <div class="footer-section">
                <h3>Crust Pizza</h3>
                <p>Australia's favorite gourmet pizza destination since 2001. From our family to yours, we're committed to delivering exceptional taste and quality in every bite.</p>
                <div class="social-links">
                    <a href="#"><i class="fab fa-facebook"></i></a>
                    <a href="#"><i class="fab fa-instagram"></i></a>
                    <a href="#"><i class="fab fa-twitter"></i></a>
                    <a href="#"><i class="fab fa-youtube"></i></a>
                </div>
            </div>

            <div class="footer-section">
                <h4>Quick Links</h4>
                <ul>
                    <li><a href="menu.php"><i class="fas fa-pizza-slice"></i> Our Menu</a></li>
                    <li><a href="build-pizza.php"><i class="fas fa-tools"></i> Build Your Pizza</a></li>
                    <li><a href="track-order.php"><i class="fas fa-truck"></i> Track Your Order</a></li>
                    <li><a href="locations.php"><i class="fas fa-map-marker-alt"></i> Find a Store</a></li>
                </ul>
            </div>

            <div class="footer-section">
                <h4>Customer Care</h4>
                <ul>
                    <li><a href="#"><i class="fas fa-phone"></i> Contact Us</a></li>
                    <li><a href="#"><i class="fas fa-question-circle"></i> FAQ</a></li>
                    <li><a href="#"><i class="fas fa-comment"></i> Feedback</a></li>
                    <li><a href="#"><i class="fas fa-file-contract"></i> Terms & Conditions</a></li>
                    <li><a href="#"><i class="fas fa-shield-alt"></i> Privacy Policy</a></li>
                </ul>
            </div>

            <div class="footer-section">
                <h4>Contact Info</h4>
                <ul>
                    <li><i class="fas fa-phone"></i> <strong>1300 278 787</strong></li>
                    <li><i class="fas fa-envelope"></i> info@crustpizza.com.au</li>
                    <li><i class="fas fa-clock"></i> Mon-Sun: 11AM - 11PM</li>
                    <li><i class="fas fa-map-marker-alt"></i> 130+ locations across Australia</li>
                </ul>
            </div>
        </div>

        <div class="footer-bottom">
            <p>© <span id="currentYear"></span> Crust Pizza. All rights reserved.</p>
        </div>
    </div>
</footer>

<script>
    // Set the current year in the footer
    document.getElementById('currentYear').textContent = new Date().getFullYear();
</script>