<?php
if (wp_get_environment_type() !== 'local') { throw new RuntimeException('Disposable local site required.'); }
foreach (array('en_US', 'de_DE') as $locale) {
    $slug = substr($locale, 0, 2);
    if (!PLL()->model->languages->get($slug)) {
        $result = PLL()->model->languages->add(array('locale'=>$locale));
        if (is_wp_error($result)) { throw new RuntimeException($result->get_error_message()); }
    }
}
$options = get_option('polylang'); $options['default_lang']='en'; $options['browser']=0;
update_option('polylang', $options);
foreach (array('lab-subscriber'=>'subscriber', 'lab-manager'=>'shop_manager', 'lab-customer'=>'customer', 'lab-contributor'=>'contributor') as $login=>$role) {
    if (!username_exists($login)) {
        $id = wp_insert_user(array('user_login'=>$login, 'user_pass'=>wp_generate_password(30), 'user_email'=>$login.'@example.test', 'role'=>$role));
        if (is_wp_error($id)) { throw new RuntimeException($id->get_error_message()); }
    }
}
