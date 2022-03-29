import React from 'react';
import ProductsList from "./ProductsList";
import {FetchProducts, FetchFilters} from "../utilits/fetchData";

const App = () => {
  //Prepare the data for the app.
  FetchFilters();
  FetchProducts();

  // Render the app.
  return (
    <ProductsList />
  )
};

export default App;
