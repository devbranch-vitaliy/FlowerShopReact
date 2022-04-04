import React from 'react';
import { createStore } from 'react-hooks-global-state';

const initialGlobalState = {
  // Main view data.
  products: [],
  includes: [],
  page: 0,
  isLoading: true,
  pager: true,

  // Filters.
  filters: {
    occasion: [{value: "_none_", name: "Occasion"}],
    type_of_flowers: [{value: "_none_", name: "Type of flowers"}],
    colors: [
      {value: "_none_", name: "Colors"},
      {value: "#ffffff", name: "White"},
      {value: "#6c187e", name: "Purple"},
      {value: "#f5e259", name: "Yellow"},
      {value: "#e3632b", name: "Orange"},
      {value: "#b70020", name: "Red"},
    ],
    characteristics: [{value: "_none_", name: "Characteristics"}],
  },
  filters_values: {
    occasion: "_none_",
    type_of_flowers: "_none_",
    colors: "_none_",
    characteristics: "_none_",
  },

  // Cart data.
  modal_cart: {
    show: false,
    product: null,
    choice: null,
  }
};

/**
 * Reducer of the global states.
 *
 * @param {Object} states
 *   Current global states.
 * @param {Object} action
 *   Incoming action.
 * @param {string} action.type
 *   The type of the action.
 * @param {array} action.products
 *   The products list of the main view.
 * @param {array} action.includes
 *   The request includes list, detailed entities of some product fields.
 * @param {Object} action.filter
 *   The updated filter of the main view.
 * @param {string} action.filter.name
 *   The name of the filter.
 * @param {string} action.filter.value
 *   The value of the filter.
 * @param {Object} action.cartProduct
 *   The product of the cart.
 * @param {Object} action.cartChoice
 *   The product of the cart.
 * @param {string} action.color
 *   The selected product color on the cart.
 * @param {Object} action.variation
 *   The selected product variation on the cart.
 *
 * @returns {{state}}
 *   Updated states.
 */
const reducer = (states, action = {
  type, products: [], includes: [], filter: {name, value}, cartProduct: {}, cartChoice: {}, color, variation
}) => {
  switch (action.type) {
    // Main view actions.
    case 'showMore': return { ...states, page: states.page + 1 };
    case 'addProducts': return { ...states, products: [...states.products, ...action.products] };
    case 'addIncludes': return { ...states, includes: [...states.includes, ...action.includes] };
    case 'filterUpdate': return {
      ...states,
      filters_values: {...states.filters_values, [action.filter.name]: action.filter.value},
      page: 0
    };
    // Modal actions.
    case 'showCart': return {
      ...states,
      modal_cart: {
        ...states.modal_cart,
        show: true,
        product: action.cartProduct,
        choice: {color: action.cartProduct.colors[0], variation: action.cartProduct.default_variation}
      }
    }
    case 'hideCart': return { ...states, modal_cart: {...states.modal_cart, show: false, product: null, choice: null}}
    case 'chooseColor': return { ...states, modal_cart: {...states.modal_cart, choice: {...states.modal_cart.choice, color: action.color}}}
    case 'chooseVariation': return { ...states, modal_cart: {...states.modal_cart, choice: {...states.modal_cart.choice, variation: action.variation}}}

    default: return states;
  }
};
export const { dispatch, setGlobalState, getGlobalState, useGlobalState } = createStore(reducer, initialGlobalState);
