    </main>
    <footer class="public-footer">
        <div class="public-footer__inner">
            <div class="public-footer__col">
                <div class="public-footer__brand">
                    <span class="public-nav__brand-mark public-nav__brand-mark--small">
                        <img src="/assets/images/logo-mark.png" alt="<?= e(PUBLIC_APP_NAME) ?>">
                    </span>
                    <?= e(PUBLIC_APP_NAME) ?>
                </div>
                <p class="public-footer__tagline">Salon management &amp; subscriptions, made simple.</p>
            </div>
            <div class="public-footer__col public-footer__col--right">
                <h3 class="public-footer__heading">Contact</h3>
                <a href="mailto:Abdilahiyh@gmail.com" class="public-footer__contact">
                    <?= icon('mail') ?> Abdilahiyh@gmail.com
                </a>
                <a href="tel:+252637939755" class="public-footer__contact">
                    <?= icon('phone') ?> +252 63 7939755
                </a>
                <a href="tel:+252657939755" class="public-footer__contact">
                    <?= icon('phone') ?> +252 65 7939755
                </a>
            </div>
        </div>
        <div class="public-footer__bottom">
            <p class="public-footer__copyright">&copy; <?= date('Y') ?> <?= e(PUBLIC_APP_NAME) ?>. All rights reserved.</p>
        </div>
    </footer>
    <script src="/assets/js/app.js"></script>
</body>
</html>
