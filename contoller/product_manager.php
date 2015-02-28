<?php 
class ControllerCatalogProductManager extends Controller
{
  private $error = array();

  private $columns = array(
    array('key' => 'selected', 'name' => 'selected', 'sortable' => false, 'searchable' => false, 'visible' => true, 'width' => '20px', 'edittype' => false),
    array('key' => 'id', 'name' => 'p.product_id', 'sortable' => true, 'searchable' => true, 'visible' => true, 'width' => '40px', 'edittype' => false),
    array('key' => 'image', 'name' => 'p.image', 'sortable' => false, 'searchable' => false, 'visible' => true, 'width' => '40px', 'edittype' => false),
    array('key' => 'imagefile', 'name' => 'p.image', 'sortable' => false, 'searchable' => false, 'visible' => false, 'width' => false, 'edittype' => 'input'),
    array('key' => 'name', 'name' => 'pd.name', 'sortable' => true, 'searchable' => true, 'visible' => true, 'width' => false, 'edittype' => 'input'),
    array('key' => 'manufacturer', 'name' => 'm.name', 'sortable' => true, 'searchable' => true, 'visible' => true, 'width' => false, 'edittype' => 'select'),
    array('key' => 'model', 'name' => 'p.model', 'sortable' => true, 'searchable' => true, 'visible' => true, 'width' => false, 'edittype' => 'input'),
    array('key' => 'sku', 'name' => 'p.sku', 'sortable' => true, 'searchable' => true, 'visible' => false, 'width' => false, 'edittype' => 'input'),
    array('key' => 'location', 'name' => 'p.location', 'sortable' => true, 'searchable' => true, 'visible' => false, 'width' => false, 'edittype' => 'input'),
    array('key' => 'keyword', 'name' => 'keyword', 'sortable' => true, 'searchable' => false, 'visible' => false, 'width' => false, 'edittype' => 'input'),
    array('key' => 'weight', 'name' => 'weight', 'sortable' => true, 'searchable' => false, 'visible' => false, 'width' => false, 'edittype' => 'input'),
    array('key' => 'weight_class', 'name' => 'weight_class', 'sortable' => false, 'searchable' => false, 'visible' => false, 'width' => false, 'edittype' => 'select'),
    array('key' => 'tax_class', 'name' => 'tax_class', 'sortable' => true, 'searchable' => false, 'visible' => false, 'width' => false, 'edittype' => 'select'),
    array('key' => 'points', 'name' => 'p.points', 'sortable' => true, 'searchable' => true, 'visible' => false, 'width' => false, 'edittype' => 'input'),
    array('key' => 'price', 'name' => 'p.price', 'sortable' => true, 'searchable' => true, 'visible' => true, 'width' => false, 'edittype' => 'input'),
    array('key' => 'price_after_tax', 'name' => 'price_after_tax', 'sortable' => true, 'searchable' => false, 'visible' => false, 'width' => false, 'edittype' => 'input'),
    array('key' => 'cost', 'name' => 'p.cost', 'sortable' => true, 'searchable' => true, 'visible' => true, 'width' => false, 'edittype' => 'input'),
    array('key' => 'quantity', 'name' => 'p.quantity', 'sortable' => true, 'searchable' => false, 'visible' => true, 'width' => '60px', 'edittype' => 'input'),
    array('key' => 'minimum', 'name' => 'p.minimum', 'sortable' => true, 'searchable' => false, 'visible' => false, 'width' => '60px', 'edittype' => 'input'),
    array('key' => 'viewed', 'name' => 'p.viewed', 'sortable' => true, 'searchable' => false, 'visible' => false, 'width' => false, 'edittype' => false),
    array('key' => 'subtract', 'name' => 'p.subtract', 'sortable' => true, 'searchable' => false, 'visible' => false, 'width' => false, 'edittype' => 'select'),
    array('key' => 'shipping', 'name' => 'p.shipping', 'sortable' => true, 'searchable' => false, 'visible' => false, 'width' => false, 'edittype' => 'select'),
    array('key' => 'status', 'name' => 'p.status', 'sortable' => true, 'searchable' => true, 'visible' => true, 'width' => false, 'edittype' => 'select'),
    array('key' => 'date_added', 'name' => 'p.date_added', 'sortable' => true, 'searchable' => true, 'visible' => false, 'width' => false, 'edittype' => false),
    array('key' => 'date_modified', 'name' => 'p.date_modified', 'sortable' => true, 'searchable' => true, 'visible' => false, 'width' => false, 'edittype' => false),
    array('key' => 'date_available', 'name' => 'p.date_available', 'sortable' => true, 'searchable' => true, 'visible' => false, 'width' => false, 'edittype' => 'input'),
    array('key' => 'sort_order', 'name' => 'p.sort_order', 'sortable' => true, 'searchable' => false, 'visible' => false, 'width' => false, 'edittype' => 'input'),
    array('key' => 'action', 'name' => 'action', 'sortable' => false, 'searchable' => false, 'visible' => true, 'width' => '60px', 'edittype' => false)
  );

  public function index()
  {
    $this->load->language('catalog/product_manager');

    $this->document->setTitle($this->language->get('heading_title'));

    $this->getList();
  }

  public function settings()
  {
    $this->load->language('catalog/product_manager');

    $this->document->setTitle($this->language->get('heading_title'));

    $this->load->model('setting/setting');

    if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validateModify())
    {
      $this->model_setting_setting->editSetting('product_manager', $this->request->post);

      $this->session->data['success'] = $this->language->get('text_setting_success');

      $this->redirect($this->ocw->buildURL('catalog/product_manager', 'token=' . $this->session->data['token'], 'SSL'));
    }

    $this->getSettings();
  }

  private function getSettings()
  {
    $this->data['settings_heading_title'] = $this->language->get('settings_heading_title');

    $this->data['text_none'] = $this->language->get('text_none');

    $this->data['entry_language'] = $this->language->get('entry_language');
    $this->data['entry_tax_rate'] = $this->language->get('entry_tax_rate');

    if ($this->ocw->getVersion() < 1.5)
    {
      $this->document->breadcrumbs = array();

      $this->document->breadcrumbs[] = array(
        'href' => $this->ocw->buildURL('common/home', 'token=' . $this->session->data['token'], 'SSL'),
        'text' => $this->language->get('text_home'),
        'separator' => FALSE
      );

      $this->document->breadcrumbs[] = array(
        'href' => $this->ocw->buildURL('catalog/product_manager', 'token=' . $this->session->data['token'], 'SSL'),
        'text' => $this->language->get('heading_title'),
        'separator' => ' :: '
      );

      $this->document->breadcrumbs[] = array(
        'text' => $this->language->get('text_settings'),
        'href' => $this->ocw->buildURL('catalog/product_manager/settings', 'token=' . $this->session->data['token'], 'SSL'),
        'separator' => ' :: '
      );
    }
    else
    {
      $this->data['breadcrumbs'] = array();

      $this->data['breadcrumbs'][] = array(
        'href' => $this->ocw->buildURL('common/home', 'token=' . $this->session->data['token'], 'SSL'),
        'text' => $this->language->get('text_home'),
        'separator' => FALSE
      );

      $this->data['breadcrumbs'][] = array(
        'href' => $this->ocw->buildURL('catalog/product_manager', 'token=' . $this->session->data['token'], 'SSL'),
        'text' => $this->language->get('heading_title'),
        'separator' => ' :: '
      );

      $this->data['breadcrumbs'][] = array(
        'text' => $this->language->get('text_settings'),
        'href' => $this->ocw->buildURL('catalog/product_manager/settings', 'token=' . $this->session->data['token'], 'SSL'),
        'separator' => ' :: '
      );
    }

    $this->data['cancel'] = $this->ocw->buildURL('catalog/product_manager', 'token=' . $this->session->data['token'], 'SSL');
    $this->data['save'] = $this->ocw->buildURL('catalog/product_manager/settings', 'token=' . $this->session->data['token'], 'SSL');

    $this->load->model('localisation/language');

    $this->data['languages'] = $this->model_localisation_language->getLanguages();

    if (isset($this->request->post['language_id']))
    {
      $this->data['language_id'] = $this->request->post['language_id'];
    } elseif ($this->config->get('language_id'))
    {
      $this->data['language_id'] = $this->config->get('language_id');
    } else
    {
      $this->data['language_id'] = $this->config->get('config_language_id');
    }

    if ($this->hasTaxRateClass())
    {
      $this->load->model('localisation/tax_rate');

      $this->data['tax_rates'] = $this->model_localisation_tax_rate->getTaxRates();

      if (isset($this->request->post['tax_rate_id']))
      {
        $this->data['tax_rate_id'] = $this->request->post['tax_rate_id'];
      } elseif ($this->config->get('tax_rate_id'))
      {
        $this->data['tax_rate_id'] = $this->config->get('tax_rate_id');
      } else
      {
        $this->data['tax_rate_id'] = 0;
      }
    }

    $this->data['hasTaxRateClass'] = $this->hasTaxRateClass();

    $this->data['token'] = $this->session->data['token'];

    $this->data['button_cancel'] = $this->language->get('button_cancel');
    $this->data['button_save'] = $this->language->get('button_save');

    if (isset($this->error['warning']))
    {
      $this->data['error_warning'] = $this->error['warning'];
    } else
    {
      $this->data['error_warning'] = '';
    }

    if (isset($this->session->data['success']))
    {
      $this->data['success'] = $this->session->data['success'];

      unset($this->session->data['success']);
    } else
    {
      $this->data['success'] = '';
    }

    $this->template = 'catalog/product_manager_settings.tpl';
    $this->children = array(
      'common/header',
      'common/footer'
    );

    if ($this->ocw->getVersion() < 1.5)  {
      $this->response->setOutput($this->render(TRUE), $this->config->get('config_compression'));
    } else {
      $this->response->setOutput($this->render());
    }
  }

  private function getList()
  {

    foreach ($this->columns as $key => $column)
    {
      if ($this->hasTaxRateClass() == false)
      {
        if ($column['key'] == 'price_after_tax') unset($this->columns[$key]);
      }
      if ($this->ocw->getVersion() >= 1.5) {
        if ($column['key'] == 'cost') unset($this->columns[$key]);
      }
    }

    $this->columns = array_values($this->columns);

    if ($this->ocw->getVersion() < 1.5)
    {
      $this->document->breadcrumbs = array();

      $this->document->breadcrumbs[] = array(
        'href' => $this->ocw->buildURL('common/home', 'token=' . $this->session->data['token'], 'SSL'),
        'text' => $this->language->get('text_home'),
        'separator' => FALSE
      );

      $this->document->breadcrumbs[] = array(
        'href' => $this->ocw->buildURL('catalog/product_manager', 'token=' . $this->session->data['token'], 'SSL'),
        'text' => $this->language->get('heading_title'),
        'separator' => ' :: '
      );
    }
    else
    {
      $this->data['breadcrumbs'] = array();

      $this->data['breadcrumbs'][] = array(
        'href' => $this->ocw->buildURL('common/home', 'token=' . $this->session->data['token'], 'SSL'),
        'text' => $this->language->get('text_home'),
        'separator' => FALSE
      );

      $this->data['breadcrumbs'][] = array(
        'href' => $this->ocw->buildURL('catalog/product_manager', 'token=' . $this->session->data['token'], 'SSL'),
        'text' => $this->language->get('heading_title'),
        'separator' => ' :: '
      );
    }
   

    $this->data['iPipe'] = 5;
    $this->data['insert'] = $this->ocw->buildURL('catalog/product/insert', 'token=' . $this->session->data['token'], 'SSL');
    $this->data['copy'] = $this->ocw->buildURL('catalog/product/copy', 'token=' . $this->session->data['token'], 'SSL');
    $this->data['delete'] = $this->ocw->buildURL('catalog/product/delete', 'token=' . $this->session->data['token'], 'SSL');
    $this->data['settings'] = $this->ocw->buildURL('catalog/product_manager/settings', 'token=' . $this->session->data['token'], 'SSL');

    $this->data['heading_title'] = $this->language->get('heading_title');

    $this->data['text_enabled'] = $this->language->get('text_enabled');
    $this->data['text_disabled'] = $this->language->get('text_disabled');
    $this->data['text_no_results'] = $this->language->get('text_no_results');
    $this->data['text_none'] = $this->language->get('text_none');
    $this->data['text_select_all'] = $this->language->get('text_select_all');
    $this->data['text_select_none'] = $this->language->get('text_select_none');

    $this->data['text_advanced_filter'] = $this->language->get('text_advanced_filter');
    $this->data['text_indicator_saving'] = addslashes($this->language->get('text_indicator_saving'));
    $this->data['text_confirm_delete'] = $this->language->get('text_confirm_delete');
    $this->data['text_print_instructions'] = $this->language->get('text_print_instructions');

    $this->data['text_results_per_page'] = $this->language->get('text_results_per_page');
    $this->data['text_search'] = $this->language->get('text_search');
    $this->data['text_toggle_columns'] = $this->language->get('text_toggle_columns');
    $this->data['text_print_instructions'] = $this->language->get('text_print_instructions');

    $this->data['entry_filter_category'] = $this->language->get('entry_filter_category');

    $this->data['button_apply_filter'] = $this->language->get('button_apply_filter');
    $this->data['button_settings'] = $this->language->get('button_settings');

    $this->data['error_no_selection'] = $this->language->get('error_no_selection');

    foreach ($this->columns as $column)
    {
      if ($column['key'] != 'selected')
      {
        $this->data['column_' . $column['key']] = $this->language->get('column_' . $column['key']);
      }
    }

    $this->data['button_copy'] = $this->language->get('button_copy');
    $this->data['button_insert'] = $this->language->get('button_insert');
    $this->data['button_delete'] = $this->language->get('button_delete');

    $this->data['token'] = $this->session->data['token'];

    if (isset($this->error['warning']))
    {
      $this->data['error_warning'] = $this->error['warning'];
    } else
    {
      $this->data['error_warning'] = '';
    }

    if (isset($this->session->data['success']))
    {
      $this->data['success'] = $this->session->data['success'];

      unset($this->session->data['success']);
    } else
    {
      $this->data['success'] = '';
    }

    $this->data['stores'] = array();

    $this->data['stores'][] = array(
      'store_id' => 0,
      'name' => $this->language->get('text_store_default')
    );

    $this->load->model('setting/store');

    $results = $this->model_setting_store->getStores();

    foreach ($results as $result)
    {
      $this->data['stores'][] = array(
        'store_id' => $result['store_id'],
        'name' => $result['name']
      );
    }

    if (count($this->data['stores']) > 1)
    {
      $this->data['stores'][] = array(
        'store_id' => 'all',
        'name' => $this->language->get('text_store_all')
      );
    }

    $this->load->model('catalog/category');

    $this->data['categories'] = $this->model_catalog_category->getCategories(0);

    if (isset($this->request->post['filter_category']))
    {
      $this->data['filter_category'] = $this->request->post['filter_category'];
      $this->session->data['filter_category'] = $this->request->post['filter_category'];
    } elseif (isset($this->session->data['filter_category']))
    {
      $this->data['filter_category'] = $this->session->data['filter_category'];
    } else
    {
      $this->data['filter_category'] = array();
    }

    $this->load->model('catalog/manufacturer');
    $this->load->model('localisation/tax_class');
    $this->load->model('localisation/weight_class');

    $bVisible = array();
    $bSortable = array('0');
    $sWidth = array();

    foreach ($this->columns as $i => &$column)
    {
      if (!$column['visible'])
      {
        $bVisible[] = $i;
      }
      if (!$column['sortable'])
      {
        $bSortable[] = $i;
      }
      if ($column['width'] != false)
      {
        $sWidth[] = array('index' => $i, 'value' => $column['width']);
      }
      if ($column['key'] == 'image')
      {
        $imageIndex = $i;
      }
      if ($column['key'] == 'action')
      {
        $actionIndex = $i;
      }
      if ($column['key'] == 'selected')
      {
        $selectedIndex = $i;
      }
      if ($column['key'] == 'id')
      {
        $idIndex = $i;
      }
      if ($column['key'] == 'price')
      {
        $priceIndex = $i;
      }
      if ($column['key'] == 'price_after_tax' && $this->hasTaxRateClass())
      {
        $priceAfterTaxIndex = $i;
      }
      if ($column['key'] == 'date_modified')
      {
        $modifiedIndex = $i;
      }
      if ($column['key'] == 'status')
      {
        $column['options'] = array('0' => $this->language->get('text_disabled'), '1' => $this->language->get('text_enabled'));
      }
      if ($column['key'] == 'manufacturer')
      {
        $manufacturers = $this->model_catalog_manufacturer->getManufacturers();
        $column['options'][0] = $this->language->get('text_none');
        foreach ($manufacturers as $manufacturer)
        {
          $column['options'][$manufacturer['manufacturer_id']] = addslashes($manufacturer['name']);
        }
      }
      if ($column['key'] == 'weight_class')
      {
        $weight_classes = $this->model_localisation_weight_class->getWeightClasses();
        foreach ($weight_classes as $weight_class)
        {
          $column['options'][$weight_class['weight_class_id']] = $weight_class['title'];
        }
      }
      if ($column['key'] == 'tax_class')
      {
        $tax_classes = $this->model_localisation_tax_class->getTaxClasses();
        $column['options'][0] = $this->language->get('text_none');
        foreach ($tax_classes as $tax_class)
        {
          $column['options'][$tax_class['tax_class_id']] = $tax_class['title'];
        }
      }
      if ($column['key'] == 'shipping')
      {
        $column['options'] = array('0' => $this->language->get('text_no'), '1' => $this->language->get('text_yes'));
      }
      if ($column['key'] == 'subtract')
      {
        $column['options'] = array('0' => $this->language->get('text_no'), '1' => $this->language->get('text_yes'));
      }
    }

    foreach ($this->columns as &$column)
    {
      if (isset($column['options']))
      {
        $column['options'] = json_encode($column['options']);
      }
    }

    $this->data['bVisible'] = implode(',', $bVisible);
    $this->data['bSortable'] = implode(',', $bSortable);
    $this->data['sWidth'] = $sWidth;
    $this->data['imageIndex'] = $imageIndex;
    $this->data['actionIndex'] = $actionIndex;
    $this->data['selectedIndex'] = $selectedIndex;
    $this->data['idIndex'] = $idIndex;
    $this->data['priceIndex'] = $priceIndex;
    $this->data['priceAfterTaxIndex'] = $this->hasTaxRateClass() ? $priceAfterTaxIndex : 0;
    $this->data['modifiedIndex'] = $modifiedIndex;

    $this->data['columns'] = $this->columns;

    $this->document->addStyle('view/javascript/jquery/dataTables/css/dataTables.css');
    $this->document->addStyle('view/javascript/jquery/dataTables/css/TableTools.css');
    $this->document->addStyle('view/javascript/jquery/dataTables/css/TableTools.print.css', 'stylesheet', 'print');
    $this->document->addStyle('view/javascript/jquery/dataTables/css/ColVis.css');
    $this->document->addScript('view/javascript/jquery/dataTables/js/jquery.dataTables.min.js');
    $this->document->addScript('view/javascript/jquery/jquery.jeditable.min.js');
    $this->document->addScript('view/javascript/jquery/dataTables/js/TableTools.min.js');
    $this->document->addScript('view/javascript/jquery/dataTables/js/ColVis.min.js');

    $this->template = 'catalog/product_manager_list.tpl';
    $this->children = array(
      'common/header',
      'common/footer'
    );

    if ($this->ocw->getVersion() < 1.5)  {
      $this->response->setOutput($this->render(TRUE), $this->config->get('config_compression'));
    } else {
      $this->response->setOutput($this->render());
    }
  }

  public function apply_filter()
  {
    $json = array();

    if (isset($this->request->post['filter_category']))
    {
      $categories = explode('|', $this->request->post['filter_category']);

      foreach ($categories as $key => $value)
      {
        if (empty($value)) unset($categories[$key]);
      }

      $this->session->data['filter_category'] = $categories;
    }

    $json['success'] = 'true';

    if ($this->ocw->getVersion() < 1.5)  {
      $this->load->library('json');
      $this->response->setOutput(Json::encode($json));
    } else {
      $this->response->setOutput(json_encode($json));
    }
  }

  public function getDataTable()
  {
    foreach ($this->columns as $key => $column)
    {
      if ($this->hasTaxRateClass() == false)
      {
        if ($column['key'] == 'price_after_tax') unset($this->columns[$key]);
      }
      if ($this->ocw->getVersion() >= 1.5)
      {
        if ($column['key'] == 'cost') unset($this->columns[$key]);
      }
    }

    $this->columns = array_values($this->columns);

    $this->load->model('catalog/product_manager');
    $this->load->language('catalog/product_manager');

    $start = $limit = '';
    if (isset($this->request->get['iDisplayStart']) && $this->request->get['iDisplayLength'] != '-1')
    {
      $start = $this->request->get['iDisplayStart'];
      $limit = $this->request->get['iDisplayLength'];
    }

    $sort = $order = '';
    if (isset($this->request->get['iSortCol_0']))
    {
      for ($i = 0; $i < intval($this->request->get['iSortingCols']); $i++)
      {
        if ($this->request->get['bSortable_' . intval($this->request->get['iSortCol_' . $i])] == "true")
        {
          if ($this->columns[intval($this->request->get['iSortCol_' . $i])]['sortable'])
          {
            $sort = $this->columns[intval($this->request->get['iSortCol_' . $i])]['name'];
            $order = strtoupper($this->request->get['sSortDir_' . $i]);
          }
        }
      }
    }

    $filters = array();
    if (isset($this->request->get['sSearch']) && $this->request->get['sSearch'] != "")
    {
      for ($i = 0; $i < count($this->columns); $i++)
      {
        if ($this->columns[$i]['searchable'])
        {
          $filters[] = array('name' => $this->columns[$i]['name'], 'keyword' => $this->request->get['sSearch']);
        }
      }
    }

    if (isset($this->session->data['filter_category']))
    {
      $filter_category = $this->session->data['filter_category'];
    } else
    {
      $filter_category = array();
    }

    $products = array();

    $sort_columns = array();

    foreach ($this->columns as $column)
    {
      if ($column['sortable'])
      {
        $sort_columns[] = $column['name'];
      }
    }

    $filter_store = ($this->request->get['store_id'] == 'all') ? null : $this->request->get['store_id'];

    $data = array(
      'filters' => $filters,
      'filter_store' => $filter_store,
      'filter_category' => $filter_category,
      'sort' => $sort,
      'order' => $order,
      'start' => $start,
      'limit' => $limit,
      'sort_columns' => $sort_columns
    );

    $this->load->model('tool/image');

    $product_total = $this->model_catalog_product_manager->getTotalProducts($data);

    $results = $this->model_catalog_product_manager->getProducts($data);

    foreach ($results as $result)
    {
      $action = array();

      $action[] = array(
        'text' => $this->language->get('text_edit'),
        'href' => $this->ocw->buildURL('catalog/product/update', 'token=' . $this->session->data['token'], 'SSL') . '&product_id=' . $result['product_id']
      );

      if ($result['image'] && file_exists(DIR_IMAGE . $result['image']))
      {
        $image = $this->model_tool_image->resize($result['image'], 40, 40);
      } else
      {
        $image = $this->model_tool_image->resize('no_image.jpg', 40, 40);
      }

      $product_specials = $this->model_catalog_product_manager->getProductSpecials($result['product_id']);

      if ($product_specials)
      {
        $special = reset($product_specials);
        if ($special['date_start'] > date('Y-m-d') || $special['date_end'] < date('Y-m-d'))
        {
          $special = FALSE;
        }
      } else
      {
        $special = FALSE;
      }

      $product = array(
        $result['product_id'],
        $result['product_id'],
        $image,
        $result['imagefile'],
        $result['name'],
        $result['manufacturer'],
        $result['model'],
        $result['sku'],
        $result['location'],
        $result['keyword'],
        $result['weight'],
        $result['weight_class'],
        $result['tax_class'],
        $result['points'],
        $result['price'],
        $result['price_after_tax'],
        $result['cost'],
        $result['quantity'],
        $result['minimum'],
        $result['viewed'],
        ($result['subtract'] ? $this->language->get('text_yes') : $this->language->get('text_no')),
        ($result['shipping'] ? $this->language->get('text_yes') : $this->language->get('text_no')),
        ($result['status'] ? $this->language->get('text_enabled') : $this->language->get('text_disabled')),
        date($this->language->get('date_format_php'), strtotime($result['date_added'])),
        date($this->language->get('date_format_php'), strtotime($result['date_modified'])),
        date($this->language->get('date_format_php'), strtotime($result['date_available'])),
        $result['sort_order'],
        $action
      );

      foreach ($product as $k => $v) {
        if ($v == "__REMOVE__") unset($product[$k]);
      }

      $product = array_values($product);

      $products[] = $product;
    }

    $json = array(
      'sEcho' => intval($this->request->get['sEcho']),
      'iTotalRecords' => $product_total,
      'iTotalDisplayRecords' => $product_total,
      'aaData' => $products
    );

    $this->data['token'] = $this->session->data['token'];

    if ($this->ocw->getVersion() < 1.5)  {
      $this->load->library('json');
      $this->response->setOutput(Json::encode($json));
    } else {
      $this->response->setOutput(json_encode($json));
    }
  }

  public function editDataTable()
  {
    foreach ($this->columns as $key => $column)
    {
      if ($this->hasTaxRateClass() == false)
      {
        if ($column['key'] == 'price_after_tax') unset($this->columns[$key]);
      }
      if ($this->ocw->getVersion() >= 1.5)
      {
        if ($column['key'] == 'cost') unset($this->columns[$key]);
      }
    }

    $this->columns = array_values($this->columns);

    $this->load->model('catalog/product_manager');
    $this->load->language('catalog/product_manager');

    $json = array();

    $json['timestamp'] = date($this->language->get('date_format_php'));

    $json['data'] = $this->request->post['old_value'];

    if (!$this->user->hasPermission('modify', 'catalog/product_manager'))
    {
      $json['error'] = $this->language->get('error_permission');
    } else
    {
      if (isset($this->request->post['value']) && isset($this->request->post['row_id']) && isset($this->request->post['column']) && $this->validateDataTable())
      {

        $data = array(
          'key' => $this->columns[$this->request->post['column']]['key'],
          'column' => $this->columns[$this->request->post['column']]['name'],
          'value' => $this->request->post['value'],
          'old_value' => $this->request->post['old_value']
        );

        $json['data'] = $this->model_catalog_product_manager->editProduct($this->request->post['row_id'], $data);

        $this->cache->delete('product');
      } else
      {
        $json['error'] = $this->error['warning'];
      }
    }

    $this->data['token'] = $this->session->data['token'];

    if ($this->ocw->getVersion() < 1.5)  {
      $this->load->library('json');
      $this->response->setOutput(Json::encode($json));
    } else {
      $this->response->setOutput(json_encode($json));
    }
  }

  private function hasTaxRateClass()
  {
    return file_exists(DIR_APPLICATION . 'model/localisation/tax_rate.php');
  }

  private function validateDataTable()
  {
    if (!$this->user->hasPermission('modify', 'catalog/product'))
    {
      $this->error['warning'] = $this->language->get('error_permission');
    }

    $column = $this->columns[$this->request->post['column']];
    $key = $column['key'];
    $value = $this->request->post['value'];

    if (($key == 'quantity' || $key == 'price') && !is_numeric($value))
    {
      $this->error['warning'] = $this->language->get('error_not_numeric');
    }

    if (!$this->error)
    {
      return TRUE;
    } else
    {
      if (!isset($this->error['warning']))
      {
        $this->error['warning'] = $this->language->get('error_required_data');
      }
      return FALSE;
    }
  }

  private function validateModify()
  {
    if (!$this->user->hasPermission('modify', 'catalog/product_manager'))
    {
      $this->error['warning'] = $this->language->get('error_permission');
    }

    if (!$this->error)
    {
      return true;
    } else
    {
      return false;
    }
  }

}

?>