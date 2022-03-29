import React from 'react';
import { createStore } from 'react-hooks-global-state';

const initialGlobalState = {
  products: [],
  page: 0,
  isLoading: true,
  pager: true,
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
  }
};
const reducer = (state, action) => {
  switch (action.type) {
    case 'showMore': return { ...state, page: state.page + 1 };
    case 'addProducts': return { ...state, products: [...state.products, ...action.products] };
    default: return state;
  }
};
export const { dispatch, setGlobalState, getGlobalState, useGlobalState } = createStore(reducer, initialGlobalState);
