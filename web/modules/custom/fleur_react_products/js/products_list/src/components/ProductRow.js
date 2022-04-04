import React from 'react';
import ProductView from "./ProductView";

const ProductRow = ({ products }) => (
  <div className="products-row-wrapper">
    <div className="products-row row">
      {products && products.map(product => (
        <ProductView key={product.id} product={product} />
      ))}
    </div>
  </div>
);

export default ProductRow;
