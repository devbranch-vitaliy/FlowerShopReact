import React from 'react';
import { createGlobalState } from 'react-hooks-global-state';

const initialGlobalState = {
  products: [],
  page: 0,
  isLoading: false,
  pager: false
};
export const { setGlobalState, getGlobalState, useGlobalState } = createGlobalState(initialGlobalState);
