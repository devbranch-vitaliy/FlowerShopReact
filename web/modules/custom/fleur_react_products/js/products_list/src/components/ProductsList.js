import React from "react";
import ProductRow from "./ProductRow";
import {dispatch, useGlobalState} from "../utilits/globals";

const ProductsList = () => {
  const [products] = useGlobalState("products");
  const [pager] = useGlobalState("pager");
  const [isLoading] = useGlobalState("isLoading");

  return (
    <div className="products-list-wrapper">
      <div className="products-list clearfix">
        {products && products.map((row, id) => (
          <ProductRow key={id} products={row} />
        ))}
      </div>
      {pager && <div className="pager">
        <button className="load-more" onClick={() => dispatch({type: "showMore"})}>{isLoading ? 'Loading...' : 'Load more products'}</button>
      </div>}
    </div>
  )
};

export default ProductsList;
