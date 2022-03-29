import React from 'react';
import ProductsList from "./ProductsList";
import FetchProducts from "../utilits/fetchData";

const App = () => {
  //Prepare the data for the app.
  FetchProducts();

  // Render the app.
  return (
    <ProductsList />
  )
};

export default App;
