import React from "react";
import ProductRow from "./ProductRow";
import ProductsPager from "./ProductsPager";
import ProductsFilters from "./ProductsFilters";
import {useGlobalState} from "../utilits/globals";

const ProductsList = () => {
  const [products] = useGlobalState("products");

  return (
    <div className="products-list-wrapper">
      <ProductsFilters />
      <div className="products-list clearfix">
        {products && products.map((row, id) => (
          <ProductRow key={id} products={row} />
        ))}
      </div>
      <ProductsPager />
    </div>
  )
};

export default ProductsList;
