import React from 'react';
import { createStore } from 'react-hooks-global-state';

const initialGlobalState = {
  products: [],
  page: 0,
  isLoading: false,
  pager: false
};
const reducer = (state, action) => {
  switch (action.type) {
    case 'showMore': return { ...state, page: state.page + 1 };
    case 'addProducts': return { ...state, products: [...state.products, ...action.products] };
    default: return state;
  }
};
export const { dispatch, setGlobalState, getGlobalState, useGlobalState } = createStore(reducer, initialGlobalState);
