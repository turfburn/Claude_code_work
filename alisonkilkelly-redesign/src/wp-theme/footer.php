</main>

<footer class="site-footer">
  <div class="wrap">
    <div class="footer__grid">
      <div class="footer__brand">
        <img src="<?php echo esc_url( ak_asset( 'img/ak-logo.png' ) ); ?>" width="64" height="64" alt="<?php echo esc_attr( get_bloginfo( 'name' ) . ' logo' ); ?>" loading="lazy">
        <p><strong style="color:#fff"><?php echo esc_html( ak_opt( 'owner' ) ); ?></strong><br><?php echo esc_html( ak_opt( 'footer_blurb' ) ); ?></p>
        <?php if ( ak_opt( 'psych_today' ) || ak_opt( 'good_therapy' ) ) : ?>
        <p><?php esc_html_e( 'A preferred therapist on', 'ak-counseling' ); ?> <a href="<?php echo esc_url( ak_opt( 'psych_today' ) ); ?>">PsychologyToday.com</a> &amp; <a href="<?php echo esc_url( ak_opt( 'good_therapy' ) ); ?>">GoodTherapy.org</a></p>
        <?php endif; ?>
        <div class="footer__social"><?php ak_social_links(); ?></div>
      </div>
      <div>
        <h2><?php esc_html_e( 'Services', 'ak-counseling' ); ?></h2>
        <ul>
          <li><a href="<?php echo esc_url( home_url( '/couplestherapy-marriagecounseling/' ) ); ?>">Marriage &amp; Couples Counseling</a></li>
          <li><a href="<?php echo esc_url( home_url( '/therapy-coaching/' ) ); ?>">Therapy &amp; Coaching</a></li>
          <li><a href="<?php echo esc_url( home_url( '/pre-marital-counseling/' ) ); ?>">Pre-Marital Counseling</a></li>
          <li><a href="<?php echo esc_url( home_url( '/infidelity/' ) ); ?>">Infidelity Recovery</a></li>
        </ul>
      </div>
      <div>
        <h2><?php esc_html_e( 'Practice', 'ak-counseling' ); ?></h2>
        <ul>
          <li><a href="<?php echo esc_url( home_url( '/about/' ) ); ?>">About Alison</a></li>
          <li><a href="<?php echo esc_url( home_url( '/what-to-expect/' ) ); ?>">What to Expect</a></li>
          <li><a href="<?php echo esc_url( home_url( '/what-clients-say/' ) ); ?>">What Clients Say</a></li>
          <li><a href="<?php echo esc_url( home_url( '/rates/' ) ); ?>">Rates</a></li>
          <li><a href="<?php echo esc_url( home_url( '/resources/' ) ); ?>">Resources</a></li>
          <li><a href="<?php echo esc_url( home_url( '/get-started/' ) ); ?>">New Clients, Get Started</a></li>
          <?php if ( ak_opt( 'portal_url' ) ) : ?><li><a href="<?php echo esc_url( ak_opt( 'portal_url' ) ); ?>">Client Portal</a></li><?php endif; ?>
        </ul>
      </div>
      <div>
        <h2><?php esc_html_e( 'Visit', 'ak-counseling' ); ?></h2>
        <ul>
          <li><a href="<?php echo esc_url( ak_opt( 'map_url' ) ); ?>"><?php echo esc_html( ak_opt( 'address_1' ) ); ?><br><?php echo esc_html( ak_opt( 'address_2' ) ); ?></a></li>
          <li><a href="tel:<?php echo esc_attr( ak_opt( 'phone_tel' ) ); ?>">Call | Text <?php echo esc_html( ak_opt( 'phone_display' ) ); ?></a></li>
          <li><a href="mailto:<?php echo esc_attr( ak_opt( 'email' ) ); ?>"><?php echo esc_html( ak_opt( 'email' ) ); ?></a></li>
          <li><?php echo nl2br( esc_html( ak_opt( 'hours' ) ) ); ?></li>
        </ul>
      </div>
    </div>
    <div class="footer__bottom">
      <span>&copy; <?php echo esc_html( get_bloginfo( 'name' ) ); ?> <?php esc_html_e( 'All rights reserved.', 'ak-counseling' ); ?></span>
      <span class="footer__crisis"><?php echo wp_kses( __( 'In a crisis? Call or text <a href="tel:988">988</a> (Suicide &amp; Crisis Lifeline) or dial 911.', 'ak-counseling' ), array( 'a' => array( 'href' => array() ) ) ); ?></span>
    </div>
  </div>
</footer>

<nav class="mobile-bar" aria-label="<?php esc_attr_e( 'Quick contact', 'ak-counseling' ); ?>">
  <a href="tel:<?php echo esc_attr( ak_opt( 'phone_tel' ) ); ?>"><?php ak_icon( 'phone' ); ?><?php esc_html_e( 'Call', 'ak-counseling' ); ?></a>
  <a href="sms:<?php echo esc_attr( ak_opt( 'phone_tel' ) ); ?>"><?php ak_icon( 'text' ); ?><?php esc_html_e( 'Text', 'ak-counseling' ); ?></a>
  <a href="mailto:<?php echo esc_attr( ak_opt( 'email' ) ); ?>?subject=Website%20Inquiry"><?php ak_icon( 'mail' ); ?><?php esc_html_e( 'Email', 'ak-counseling' ); ?></a>
  <a class="is-cta" href="<?php echo esc_url( home_url( '/get-started/' ) ); ?>"><?php ak_icon( 'calendar' ); ?><?php esc_html_e( 'Book', 'ak-counseling' ); ?></a>
</nav>

<?php wp_footer(); ?>
</body>
</html>
