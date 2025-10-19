<?php
// footer.php
?>
    </main>
    <footer class="site-footer">
        <div class="footer-container">
            <div class="footer-brand">
                <p>&copy; <?php echo date('Y'); ?> <strong>Adwadifo</strong>. All Rights Reserved.</p>
                <p>Connecting vendors and customers — one fresh product at a time.</p>
            </div>

            <div class="footer-social">
                <div class="footer-contact">
                <a href="about.php">Contact Us</a>
                </div>
                <a href="https://facebook.com" target="_blank"><img src="assets/icons/facebook.svg" alt="Facebook"></a>
                <a href="https://instagram.com" target="_blank"><img src="assets/icons/instagram.svg" alt="Instagram"></a>
                <a href="https://wa.me/233599891070" target="_blank"><img src="assets/icons/whatsapp.svg" alt="WhatsApp"></a>
                <div class="footer-gmail">
                <a href="#">adwadifolocalmart@gmail.com</a>

                </div>
            </div>

            <div class="footer-newsletter">
                <form method="post" action="subscribe.php">
                    <label for="newsletter">Subscribe to our newsletter</label>
                    <input type="email" id="newsletter" name="email" placeholder="Enter email" required>
                    <button type="submit">Subscribe</button>
                </form>
            </div>
        </div>
    </footer>
</body>
</html>

<style>
    .site-footer {
        background: #055828;
        color: white;
        padding: 2rem 1rem;
        font-size: 0.95rem;
        margin-top: auto;
    }

    .footer-container {
        max-width: 1200px;
        margin: 0 auto;
        display: flex;
        flex-wrap: wrap;
        justify-content: space-between;
        gap: 2rem;
        align-items: flex-start;
    }

    .footer-brand p {
        margin: 0.3rem 0;
    }

    .footer-social a {
        margin-right: 10px;
        display: inline-block;
    }

    .footer-social img {
        width: 24px;
        height: 24px;
        filter: brightness(0) invert(1);
        transition: transform 0.2s ease;
    }

    .footer-social img:hover {
        transform: scale(1.1);
    }

    .footer-newsletter form {
        display: flex;
        flex-direction: column;
        gap: 0.5rem;
    }

    .footer-newsletter input {
        padding: 0.5rem;
        border-radius: 4px;
        border: none;
        font-size: 0.9rem;
    }

    .footer-newsletter button {
        background: #1e8449;
        color: white;
        border: none;
        padding: 0.5rem;
        border-radius: 4px;
        cursor: pointer;
    }

    .footer-newsletter button:hover {
        background: #145a32;
    }

    .footer-contact a {
        color: #ffffff;
        text-decoration: underline;
        font-weight: 500;
    }

    .footer-gmail a {
        color: #ffffff;
        text-decoration: underline;
        font-weight: 500;
    }

    @media (max-width: 768px) {
        .footer-container {
            flex-direction: column;
            align-items: flex-start;
        }

        .footer-social {
            margin-top: 1rem;
        }
    }
</style>
