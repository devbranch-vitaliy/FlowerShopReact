import {DrupalJsonApiParams} from 'drupal-jsonapi-params';

/**
 * The helper function for making requests to a Drupal backend.
 *
 * @param {string} endpoint
 *  The name of the end point you want to use.
 *  @param {Object} [parameters={}]
 *  Route string construction parameters.
 * @return {Promise}
 *  Result of the fetch operation.
 */
export function request(endpoint, parameters = {}) {
  let url = '/jsonapi/';
  const apiParams = new DrupalJsonApiParams();

  switch (endpoint) {
    case 'products_list':
      apiParams
        .addFields('commerce_product--default', ['title', 'field_image', 'default_variation'])
        .addInclude(['default_variation', 'field_image'])
        .addPageLimit(3)
        .addCustomParam({ page: { offset: parameters.page ?? 0 }})
      url += 'commerce_product/default?' + apiParams.getQueryString();
      break;

    default:
      break;
  }

  return fetch(url)
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
 * @param {array} includes[{}]
 *  The list of the includes that loaded via request().
 * @return {Object|false}
 *  Result of the field data if we found it or false in the other case.
 */
export function findRelationship(relationship, includes) {
  if (!relationship) {
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
