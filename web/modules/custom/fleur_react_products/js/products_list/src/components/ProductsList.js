import React from "react";
import ProductRow from "./ProductRow";
import ProductsPager from "./ProductsPager";
import ProductsFilters from "./ProductsFilters";
import {useGlobalState} from "../utilits/globals";
import ModalCart from "./ModalCart";

const ProductsList = () => {
  const [products] = useGlobalState("products");

  return (
    <div className="products-list-wrapper">
      <ModalCart />
      <div className={"title"}>
        <h1 className="text-color-black hidden-xs">Our Products</h1>
        <h2 className="text-color-black visible-xs">Our Products</h2>
      </div>
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
