<?php
class ModelCatalogProductManager extends Model {
		
	public function getProduct($product_id) {
    $language_id = $this->config->get('language_id') ? $this->config->get('language_id') : $this->config->get('config_language_id');
		$query = $this->db->query("SELECT DISTINCT *, (SELECT keyword FROM " . DB_PREFIX . "url_alias WHERE query = 'product_id=" . (int)$product_id . "') AS keyword FROM " . DB_PREFIX . "product p LEFT JOIN " . DB_PREFIX . "product_description pd ON (p.product_id = pd.product_id) WHERE p.product_id = '" . (int)$product_id . "' AND pd.language_id = '" . (int)$language_id . "'");
				
		return $query->row;
	}
	
	public function editProduct($product_id, $data) {

    $language_id = $this->config->get('language_id') != null ? $this->config->get('language_id') : $this->config->get('config_language_id');

		$return = $data['old_value'];
				
		if ($data['key'] == 'manufacturer') {
			if ($data['value'] == 0) { 
				$return = "";
			} else {			
				$result = $this->db->query("SELECT name FROM " . DB_PREFIX . "manufacturer WHERE manufacturer_id = '" . (int)$data['value'] . "'");
				
				$return = $result->row['name'];
			}
			$sql = "UPDATE " . DB_PREFIX . "product SET manufacturer_id = '" . (int)$data['value'] . "' WHERE product_id = '" . (int)$product_id . "'";	
		}

    if ($data['key'] == 'imagefile') {
			$sql = "UPDATE " . DB_PREFIX . "product SET image = '" . $this->db->escape($data['value']) . "' WHERE product_id = '" . (int)$product_id . "'";
			$return = $data['value'];
		}

    if ($data['key'] == 'name') {
			$sql = "UPDATE " . DB_PREFIX . "product_description SET name = '" . $this->db->escape($data['value']) . "' WHERE product_id = '" . (int)$product_id . "' AND language_id = '" . (int)$language_id . "'";
			$return = $data['value'];
		}

		if ($data['key'] == 'model') {
			$sql = "UPDATE " . DB_PREFIX . "product SET model = '" . $this->db->escape($data['value']) . "' WHERE product_id = '" . (int)$product_id . "'";
			$return = $data['value'];	
		}
		
		if ($data['key'] == 'sku') {
			$sql = "UPDATE " . DB_PREFIX . "product SET sku = '" . $this->db->escape($data['value']) . "' WHERE product_id = '" . (int)$product_id . "'";
			$return = $data['value'];	
		}
		
		if ($data['key'] == 'location') {
			$sql = "UPDATE " . DB_PREFIX . "product SET location = '" . $this->db->escape($data['value']) . "' WHERE product_id = '" . (int)$product_id . "'";
			$return = $data['value'];	
		}
		
		if ($data['key'] == 'keyword') {
			$this->db->query("DELETE FROM " . DB_PREFIX . "url_alias WHERE query = 'product_id=" . (int)$product_id. "'");
			$sql = "INSERT INTO " . DB_PREFIX . "url_alias SET query = 'product_id=" . (int)$product_id . "', keyword = '" . $this->db->escape($data['value']) . "'";
			$return = $data['value'];	
		}
    
    if ($data['key'] == 'weight') {
			$sql = "UPDATE " . DB_PREFIX . "product SET weight = '" . $this->db->escape($data['value']) . "' WHERE product_id = '" . (int)$product_id . "'";
			$return = $data['value'];	
		}
    
    if ($data['key'] == 'weight_class') {
			if ($data['value'] == 0) { 
				$return = "";
			} else {			
				$result = $this->db->query("SELECT title FROM " . DB_PREFIX . "weight_class_description WHERE weight_class_id = '" . (int)$data['value'] . "' AND language_id = '" . (int)$language_id . "'");
				
				$return = $result->row['title'];
			}
			$sql = "UPDATE " . DB_PREFIX . "product SET weight_class_id = '" . (int)$data['value'] . "' WHERE product_id = '" . (int)$product_id . "'";	
		}
		
		if ($data['key'] == 'tax_class') {
			if ($data['value'] == 0) { 
				$return = "";
			} else {			
				$result = $this->db->query("SELECT title FROM " . DB_PREFIX . "tax_class WHERE tax_class_id = '" . (int)$data['value'] . "'");
				
				$return = $result->row['title'];
			}
			$sql = "UPDATE " . DB_PREFIX . "product SET tax_class_id = '" . (int)$data['value'] . "' WHERE product_id = '" . (int)$product_id . "'";	
		}

    if ($data['key'] == 'points') {
			$sql = "UPDATE " . DB_PREFIX . "product SET points = '" . (int)$data['value'] . "' WHERE product_id = '" . (int)$product_id . "'";
			$return = $data['value'];
		}

    if ($data['key'] == 'price')
    {
      if ($this->hasTaxRateClass())
      {
        $this->load->model('localisation/tax_rate');

        $taxedAmount = $data['value'];
        if ($this->config->get('tax_rate_id'))
        {
          $tax_rate = $this->model_localisation_tax_rate->getTaxRate($this->config->get('tax_rate_id'));

          if ($tax_rate['type'] == 'F')
          {
            $taxedAmount += $tax_rate['rate'];
          }
          elseif ($tax_rate['type'] == 'P')
          {
            $taxedAmount += ($data['value'] / 100 * $tax_rate['rate']);
          }
        }

        $sql = "UPDATE " . DB_PREFIX . "product SET price = '" . (float)$data['value'] . "' WHERE product_id = '" . (int)$product_id . "'";
        $return = number_format($data['value'], 4) . '|' . number_format($taxedAmount, 4);
      }
      else
      {
        $sql = "UPDATE " . DB_PREFIX . "product SET price = '" . (float)$data['value'] . "' WHERE product_id = '" . (int)$product_id . "'";
        $return = $data['value'];
      }
    }

    if ($data['key'] == 'price_after_tax' && $this->hasTaxRateClass()) {
      $this->load->model('localisation/tax_rate');

      $untaxedAmount = $data['value'];
      if ($this->config->get('tax_rate_id')) {
        $tax_rate = $this->model_localisation_tax_rate->getTaxRate($this->config->get('tax_rate_id'));

        if ($tax_rate['type'] == 'F') {
          $untaxedAmount -= $tax_rate['rate'];
        } elseif ($tax_rate['type'] == 'P') {
          $untaxedAmount = $data['value'] / (($tax_rate['rate'] / 100) + 1);
        }
      }

			$sql = "UPDATE " . DB_PREFIX . "product SET price = '" . (float)$untaxedAmount . "' WHERE product_id = '" . (int)$product_id . "'";
			$return = number_format($untaxedAmount, 4) . '|' . number_format($data['value'], 4);
		}

    if ($data['key'] == 'cost') {
			$sql = "UPDATE " . DB_PREFIX . "product SET cost = '" . $this->db->escape($data['value']) . "' WHERE product_id = '" . (int)$product_id . "'";
			$return = number_format($data['value'], 4);
		}

		if ($data['key'] == 'quantity') {
			$sql = "UPDATE " . DB_PREFIX . "product SET quantity = '" . (int)$data['value'] . "' WHERE product_id = '" . (int)$product_id . "'";
			$return = $data['value'];	
		}
		
		if ($data['key'] == 'minimum') {
			$sql = "UPDATE " . DB_PREFIX . "product SET minimum = '" . (int)$data['value'] . "' WHERE product_id = '" . (int)$product_id . "'";
			$return = $data['value'];	
		}

		if ($data['key'] == 'subtract') {
			$sql = "UPDATE " . DB_PREFIX . "product SET subtract = '" . (int)$data['value'] . "' WHERE product_id = '" . (int)$product_id . "'";
			
			$return = ($data['value']) ? $this->language->get('text_yes') : $this->language->get('text_no');
		}
		
		if ($data['key'] == 'shipping') {
			$sql = "UPDATE " . DB_PREFIX . "product SET shipping = '" . (int)$data['value'] . "' WHERE product_id = '" . (int)$product_id . "'";
			
			$return = ($data['value']) ? $this->language->get('text_yes') : $this->language->get('text_no');
		}
		
		if ($data['key'] == 'status') {
			$sql = "UPDATE " . DB_PREFIX . "product SET status = '" . (int)$data['value'] . "' WHERE product_id = '" . (int)$product_id . "'";
			
			$return = ($data['value']) ? $this->language->get('text_enabled') : $this->language->get('text_disabled');
		}
		
		if ($data['key'] == 'sort_order') {
			$sql = "UPDATE " . DB_PREFIX . "product SET sort_order = '" . (int)$data['value'] . "' WHERE product_id = '" . (int)$product_id . "'";
			$return = $data['value'];	
		}
		
		if ($data['key'] == 'date_available') {
			$sql = "UPDATE " . DB_PREFIX . "product SET date_available = '" . date($this->language->get('date_format_php'), strtotime($data['value'])) . "' WHERE product_id = '" . (int)$product_id . "'";
			$return = date($this->language->get('date_format_php'), strtotime($data['value']));	
		}
		
		if ($sql) {
			$this->db->query($sql);
			$this->db->query("UPDATE " . DB_PREFIX . "product SET date_modified = NOW() WHERE product_id = '" . (int)$product_id . "'");
		}
		
		return $return;
	}
	
	public function getProducts($data = array()) {
    $language_id = $this->config->get('language_id') ? $this->config->get('language_id') : $this->config->get('config_language_id');

		if ($data) {
			$sql = "SELECT DISTINCT p.*, pd.*, m.name as manufacturer, wcd.title as weight_class, tc.title as tax_class FROM " . DB_PREFIX . "product p LEFT JOIN " . DB_PREFIX . "product_description pd ON (p.product_id = pd.product_id) LEFT JOIN " . DB_PREFIX . "manufacturer m ON (p.manufacturer_id = m.manufacturer_id) LEFT JOIN " . DB_PREFIX . "weight_class_description wcd ON (p.weight_class_id = wcd.weight_class_id) LEFT JOIN " . DB_PREFIX . "tax_class tc ON (p.tax_class_id = tc.tax_class_id) LEFT JOIN " . DB_PREFIX . "product_to_category p2c ON (p.product_id = p2c.product_id) LEFT JOIN " . DB_PREFIX . "product_to_store p2s ON (p.product_id = p2s.product_id) WHERE pd.language_id = '" . (int)$language_id . "' AND wcd.language_id = '" . (int)$language_id . "'";
			
			if (isset($data['filter_name']) && !is_null($data['filter_name'])) {
				$sql .= " AND LCASE(pd.name) LIKE '%" . $this->db->escape(strtolower($data['filter_name'])) . "%'";
			}

			if (isset($data['filter_model']) && !is_null($data['filter_model'])) {
				$sql .= " AND LCASE(p.model) LIKE '%" . $this->db->escape(strtolower($data['filter_model'])) . "%'";
			}

			if (isset($data['filter_price']) && !is_null($data['filter_price'])) {
				$sql .= " AND LCASE(p.price) LIKE '" . $this->db->escape(strtolower($data['filter_price'])) . "%'";
			}

			if (isset($data['filter_quantity']) && !is_null($data['filter_quantity'])) {
				$sql .= " AND p.quantity = '" . $this->db->escape($data['filter_quantity']) . "'";
			}
			
			if (isset($data['filter_status']) && !is_null($data['filter_status'])) {
				$sql .= " AND p.status = '" . (int)$data['filter_status'] . "'";
			}
			
			if (isset($data['filter_store']) && !is_null($data['filter_store'])) {
				$sql .= " AND p2s.store_id = '" . (int)$data['filter_store'] . "'";
			}

			if (isset($data['filter_category']) && count($data['filter_category']) > 0) {
				$sql .= " AND p2c.category_id IN (" . implode(',', $data['filter_category']) . ")";
			}
			
			if (isset($data['filters']) && count($data['filters']) > 1) {
				$sql .= " AND (";
				foreach ($data['filters'] as $filter) {
					$sql .= 'UPPER(' . $filter['name'] . ") LIKE UPPER('%" . $this->db->escape($filter['keyword']) . "%') OR ";
				}	
				$sql = substr_replace($sql, "", -3);
				$sql .= ")";
			}
			
			$sort_data = $data['sort_columns'];
			
			if (isset($data['sort']) && in_array($data['sort'], $sort_data) && $data['sort'] != 'keyword' && $data['sort'] != 'price_after_tax') {
				$sql .= " ORDER BY " . $data['sort'];	
			} elseif ($data['sort'] != 'keyword') {
        $sql .= " ORDER BY p.price";
      } else {
				$sql .= " ORDER BY pd.name";	
			}
			
			if (isset($data['order']) && ($data['order'] == 'DESC')) {
				$sql .= " DESC";
			} else {
				$sql .= " ASC";
			}
		
			if (isset($data['start']) || isset($data['limit'])) {
				if ($data['start'] < 0) {
					$data['start'] = 0;
				}				

				if ($data['limit'] < 1) {
					$data['limit'] = 20;
				}	
			
				$sql .= " LIMIT " . (int)$data['start'] . "," . (int)$data['limit'];
			}	
			
			$query = $this->db->query($sql);
		
			$product_data = array();
			
			foreach ($query->rows as $result) {
				
				$keyword_query = $this->db->query("SELECT keyword FROM " . DB_PREFIX . "url_alias WHERE query = 'product_id=" . (int)$result['product_id'] . "'");
				
				$keyword = ($keyword_query->num_rows) ? $keyword_query->row['keyword'] : '';

        if ($this->hasTaxRateClass())
        {
          $this->load->model('localisation/tax_rate');

          $taxedAmount = $result['price'];
          if ($this->config->get('tax_rate_id')) {
            $tax_rate = $this->model_localisation_tax_rate->getTaxRate($this->config->get('tax_rate_id'));

            if ($tax_rate['type'] == 'F') {
              $taxedAmount += $tax_rate['rate'];
            } elseif ($tax_rate['type'] == 'P') {
              $taxedAmount += ($result['price'] / 100 * $tax_rate['rate']);
            }
          }
        }

				$product_data[] = array(
					'product_id' => $result['product_id'],
					'image' => $result['image'],
					'imagefile' => $result['image'],
					'name' => $result['name'],
					'manufacturer' => $result['manufacturer'],
					'model' => $result['model'],
					'sku' => $result['sku'],
					'location' => $result['location'],
					'keyword' => $keyword,
					'weight' => $result['weight'],
					'weight_class' => $result['weight_class'],
					'tax_class' => $result['tax_class'],
          'points' => $this->ocw->getVersion() >= 1.5 ? $result['points'] : "__REMOVE__",
					'price' => $result['price'],
          'price_after_tax' => $this->hasTaxRateClass() ? number_format($taxedAmount, 4) : "__REMOVE__",
          'cost' => $this->ocw->getVersion() < 1.5 ? $result['cost'] : "__REMOVE__",
					'quantity' => $result['quantity'],
					'minimum' => $result['minimum'],
					'viewed' => $result['viewed'],
					'subtract' => $result['subtract'],
					'shipping' => $result['shipping'],
					'status' => $result['status'],
					'date_added' => $result['date_added'],
					'date_modified' => $result['date_modified'],
					'date_available' => $result['date_available'],
					'sort_order' => $result['sort_order']				
				);
			}

			if (isset($data['sort']) && $data['sort'] == 'keyword') {
				if (isset($data['order']) && ($data['order'] == 'DESC')) {
					$sort_order = SORT_DESC;
				} else {
					$sort_order = SORT_ASC;
				}
				$product_data = $this->array_sort($product_data, 'keyword', $sort_order);
			}
			
			return $product_data;
		} 
	}
	
	public function getProductSpecials($product_id) {
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "product_special WHERE product_id = '" . (int)$product_id . "' ORDER BY priority, price");
		
		return $query->rows;
	}

	public function getTotalProducts($data = array()) {
    $language_id = $this->config->get('language_id') ? $this->config->get('language_id') : $this->config->get('config_language_id');
    
		$sql = "SELECT COUNT(DISTINCT p.product_id) AS total FROM " . DB_PREFIX . "product p LEFT JOIN " . DB_PREFIX . "product_description pd ON (p.product_id = pd.product_id) LEFT JOIN " . DB_PREFIX . "manufacturer m ON (p.manufacturer_id = m.manufacturer_id) LEFT JOIN " . DB_PREFIX . "product_to_category p2c ON (p.product_id = p2c.product_id) LEFT JOIN " . DB_PREFIX . "product_to_store p2s ON (p.product_id = p2s.product_id) WHERE pd.language_id = '" . (int)$language_id . "'";
		
		if (isset($data['filter_name']) && !is_null($data['filter_name'])) {
			$sql .= " AND LCASE(pd.name) LIKE '%" . $this->db->escape(strtolower($data['filter_name'])) . "%'";
		}

		if (isset($data['filter_model']) && !is_null($data['filter_model'])) {
			$sql .= " AND LCASE(p.model) LIKE '%" . $this->db->escape(strtolower($data['filter_model'])) . "%'";
		}

		if (isset($data['filter_price']) && !is_null($data['filter_price'])) {
			$sql .= " AND LCASE(p.price) LIKE '" . $this->db->escape(strtolower($data['filter_price'])) . "%'";
		}

		if (isset($data['filter_quantity']) && !is_null($data['filter_quantity'])) {
			$sql .= " AND p.quantity = '" . $this->db->escape($data['filter_quantity']) . "'";
		}
		
		if (isset($data['filter_status']) && !is_null($data['filter_status'])) {
			$sql .= " AND p.status = '" . (int)$data['filter_status'] . "'";
		}
		
		if (isset($data['filter_store']) && !is_null($data['filter_store'])) {
			$sql .= " AND p2s.store_id = '" . (int)$data['filter_store'] . "'";
		}
		
		if (isset($data['filter_category']) && count($data['filter_category']) > 0) {
				$sql .= " AND p2c.category_id IN (" . implode(',', $data['filter_category']) . ")";
			}
		
		if (isset($data['filters']) && count($data['filters']) > 1) {
			$sql .= " AND (";
			foreach ($data['filters'] as $filter) {
				$sql .= 'UPPER(' . $filter['name'] . ") LIKE UPPER('%" . $this->db->escape($filter['keyword']) . "%') OR ";
			}	
			$sql = substr_replace($sql, "", -3);
			$sql .= ")";
		}

		$query = $this->db->query($sql);
		
		return $query->row['total'];
	}

	public function array_sort($array, $on, $order=SORT_ASC)
	{
	    $new_array = array();
	    $sortable_array = array();
	
	    if (count($array) > 0) {
	        foreach ($array as $k => $v) {
	            if (is_array($v)) {
	                foreach ($v as $k2 => $v2) {
	                    if ($k2 == $on) {
	                        $sortable_array[$k] = $v2;
	                    }
	                }
	            } else {
	                $sortable_array[$k] = $v;
	            }
	        }
	
	        switch ($order) {
	            case SORT_ASC:
	                asort($sortable_array);
	            break;
	            case SORT_DESC:
	                arsort($sortable_array);
	            break;
	        }
	
	        foreach ($sortable_array as $k => $v) {
	            $new_array[$k] = $array[$k];
	        }
	    }
	
	    return $new_array;
	}

  private function hasTaxRateClass()
  {
    return file_exists(DIR_APPLICATION . 'model/localisation/tax_rate.php');
  }
	
}
?>