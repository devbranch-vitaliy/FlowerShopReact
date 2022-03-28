import React from 'react';
import ProductView from "./ProductView";

const ProductRow = ({ key, products }) => (
  <div className="products-row row" key={key}>
    {products && products.map(product => (
      <ProductView product={product} />
    ))}
  </div>
);

export default ProductRow;
