import React from 'react';

const ProductView = ({ product }) => (
  <div className="col-xs-12 col-sm-4">
    <div className="product_view" key={product.id}>
      <a className="product_content" href={product.path}>
        <img
          className="img-responsive"
          src={product.field_image.relationship.image_style_uri.find(e => (e.product_view_768x886)).product_view_768x886}
          typeof="foaf:Image"
          alt={product.field_image.data.meta.alt}
        />
        <div className="product-info black-bg">
          <div className="product-name">{product.title}</div>
          <h4 className="text-color-white">{product.default_variation.relationship.price.formatted}</h4>
        </div>
        <div className="hover-bg">
          <div className="hover-bg-border">
            <div className="hover-bg-text-wrapper">
              <div className="hover-bg-text label-big">
                <span>"See product"</span>
                <i className="fleur-font-icon-16-arrow-right-primary"></i>
              </div>
            </div>
          </div>
        </div>
      </a>
    </div>
  </div>
);

export default ProductView;