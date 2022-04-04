import {DrupalJsonApiParams} from 'drupal-jsonapi-params';
import {getGlobalState} from "./globals";

/**
 * The helper function for making requests to a Drupal backend.
 *
 * @param {string} endpoint
 *  The name of the end point you want to use.
 * @param {Object} [parameters={}]
 *  Route string construction parameters.
 * @param {int} [parameters.page=0]
 *  Current page.
 * @param {int} [parameters.perPage=6]
 *  Items per page.
 * @param {array} [parameters.filters=[]]
 *  An array with filters for the query.
 * @return {Promise}
 *  Result of the fetch operation.
 */
export function request(endpoint, parameters = { page: 0, perPage: 6, filters: []}) {
  let url = '/jsonapi/';
  const apiParams = new DrupalJsonApiParams();
  const options = {}
  options.headers = {};

  switch (endpoint) {
    case 'products_list':
      const fields = ['drupal_internal__product_id', 'title', 'path', 'field_colors', 'field_image', 'default_variation', 'variations'];
      apiParams
        .addInclude(['default_variation', 'field_image', 'variations'])
        .addCustomParam({ page: {
          offset: (parameters.page ?? 0) * parameters.perPage,
          limit: parameters.perPage
        }})
      for (const [filter_name, value] of Object.entries(parameters.filters)) {
        if (value !== '_none_') {
          const field_name = 'field_' + filter_name + (filter_name !== 'colors' ? '.id' : '');
          apiParams.addFilter(field_name, value);
          fields.push(field_name);
        }
      }
      apiParams.addFields('commerce_product--default', fields);
      url += 'commerce_product/default?' + apiParams.getQueryString();
      break;

    case 'taxonomy_term':
      apiParams
        .addFields('taxonomy_term--' + parameters.name, ['id', 'name'])
        .addSort('weight');
      url += 'taxonomy_term/' + parameters.name + '?' + apiParams.getQueryString();
      break;

    case 'add_to_cart':
      const modal_cart = getGlobalState('modal_cart');
      const data = [{
        purchased_entity_type: "commerce_product_variation",
        purchased_entity_id: getRelationshipEntity(modal_cart.choice.variation).drupal_internal__variation_id,
        quantity: 1,
        extended_fields: {
          field_color: {color: modal_cart.choice.color, opacity: 1}
        },
      }]
      url = '/cart/add';
      options.method = 'POST';
      options.headers['Content-Type'] = 'application/json';
      options.body = JSON.stringify(data);
      break;

    default:
      break;
  }

  return fetch(url, options)
    .then(res => {
      if (![200, 201, 204].includes(res.status)) {
        throw Error('could not fetch the data for that resource');
      }
      return res.json();
    })
}

/**
 * The helper function to find a field data in the includes.
 *
 * @param {Object} relationship
 *  The data of the relationship field object loaded via request().
 * @return {Object|false}
 *  Result of the field data if we found it or false in the other case.
 */
export function getRelationshipEntity(relationship) {
  if (!relationship) {
    return false;
  }
  const includes = getGlobalState('includes');
  if (!includes.length) {
    return false;
  }

  const relationship_data = includes.find((element) => {
    return element.type === relationship.type && element.id === relationship.id;
  })
  if (typeof relationship_data === 'undefined') {
    return false
  }
  if (!relationship_data.attributes) {
    return false;
  }
  return relationship_data.attributes;
}
