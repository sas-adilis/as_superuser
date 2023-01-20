<?php

    class AS_SuperUser extends Module
    {
        public function __construct()
        {
            $this->name = 'as_superuser';
            $this->tab = 'administration';
            $this->version = '1.0.0';
            $this->author = 'Adilis';
            $this->need_instance = 0;
            $this->bootstrap = true;
            $this->ps_versions_compliancy = ['min' => '1.7', 'max' => _PS_VERSION_];

            $this->displayName = $this->l('Super User');
            $this->description = $this->l('You are THE super user!');

            parent::__construct();
        }

        public function install()
        {
            return parent::install() &&
                $this->registerHook('displayAdminOrderSide') &&
                $this->registerHook('displayAdminCustomers') &&
                $this->registerHook('displayBackOfficeTop');
        }

        public function hookDisplayAdminOrderSide($params)
        {
            $order = new Order($params['id_order']);
            if (!Validate::isLoadedObject($order)) {
                return;
            }

            $customer = new Customer($order->id_customer);
            if (!Validate::isLoadedObject($customer)) {
                return;
            }

            $cookie = $this->getFrontCookie();
            $this->context->smarty->assign([
                'customer' => $customer,
                'is_logged' => $cookie->logged && (int)$cookie->id_customer == $customer->id,
            ]);
            return $this->display(__FILE__, 'views/templates/admin/order.tpl');
        }

        public function hookDisplayAdminCustomers($params)
        {
            $customer = new Customer($params['id_customer']);
            if (!Validate::isLoadedObject($customer)) {
                return;
            }

            $cookie = $this->getFrontCookie();
            $this->context->smarty->assign([
                'customer' => $customer,
                'is_logged' => $cookie->logged && (int)$cookie->id_customer == $customer->id,
            ]);
            return $this->display(__FILE__, 'views/templates/admin/customer.tpl');
        }

        public function hookDisplayBackOfficeTop()
        {
            if (Tools::isSubmit('submitSuperUser')) {

                $customer = new Customer((int)Tools::getValue('id_customer'));
                if (!Validate::isLoadedObject($customer)) {
                    $this->context->controller->errors[] = $this->l('Customer not found');
                    return;
                }

                $cookie = $this->getFrontCookie();

                if ($cookie->logged) {
                    $cookie->logout();
                }

                Tools::setCookieLanguage();
                Tools::switchLanguage();
                $cookie->id_customer = (int)$customer->id;
                $cookie->customer_lastname = $customer->lastname;
                $cookie->customer_firstname = $customer->firstname;
                $cookie->logged = 1;
                $cookie->check_cgv = 1;
                $cookie->is_guest = $customer->isGuest();
                $cookie->passwd = $customer->passwd;
                $cookie->email = $customer->email;

                if (Configuration::get('PS_CART_FOLLOWING') AND (empty($cookie->id_cart) OR Cart::getNbProducts($cookie->id_cart) == 0)) {
                    $cookie->id_cart = Cart::lastNoneOrderedCart($customer->id);
                }

                $cookie->registerSession(new CustomerSession());
                $redirect_url = Tools::getCurrentUrlProtocolPrefix().Tools::getHttpHost().$_SERVER['REQUEST_URI'];
                Tools::redirectAdmin($redirect_url);
            }

            if (Tools::isSubmit('submitSuperUserLogout')) {
                $cookie = $this->getFrontCookie();
                if ($cookie->logged) {
                    $cookie->logout();
                }
            }
        }

        private function getFrontCookie() {
            $cookie_lifetime = (int)(defined('_PS_ADMIN_DIR_') ? Configuration::get('PS_COOKIE_LIFETIME_BO') : Configuration::get('PS_COOKIE_LIFETIME_FO'));
            $cookie_lifetime = time() + (max($cookie_lifetime, 1) * 3600);
            if ($this->context->shop->getGroup()->share_order) {
                $cookie = new Cookie('ps-sg' . $this->context->shop->getGroup()->id, '', $cookie_lifetime, $this->context->shop->getUrlsSharedCart());
            } else {
                $domains = null;
                if ($this->context->shop->domain != $this->context->shop->domain_ssl) {
                    $domains = array($this->context->shop->domain_ssl, $this->context->shop->domain);
                }
                $cookie = new Cookie('ps-s'.$this->context->shop->id, '', $cookie_lifetime, $domains);
            }
            return $cookie;
        }
    }