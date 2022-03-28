import React, {useEffect, useState} from "react";
import { request, attachRelationship } from "../utilits/api";
import ProductRow from "./ProductRow";

const ProductsList = () => {

  const [data, setData] = useState([]);
  const [page, setPage] = useState(0);
  const [isLoading, setIsLoading] = useState(false);
  const [errorMsg, setErrorMsg] = useState('');

  useEffect(() => {
    const perRow = 3;
    setIsLoading(true);

    request('products_list', { page: 0 , perPage: perRow})
      .then(products => {
        let products_data = [];

        // Fill products data.
        products.data.map((product) => {
          products_data.push({
            id: product.attributes.drupal_internal__product_id,
            title: product.attributes.title,
            path: product.attributes.path.alias,
            default_variation: attachRelationship(product.relationships.default_variation, products.included),
            field_image: attachRelationship(product.relationships.field_image, products.included),
          });
        })

        // Split data to rows.
        products_data = products_data.map((e, i) => {
          return i % perRow === 0 ? products_data.slice(i, i + perRow) : null;
        }).filter(e => { return e; });

        // Store data.
        setData([...data, ...products_data]);
      })
      .catch(err => {
        if (err.name === 'AbortError') {
          console.log('fetch aborted')
        } else {
          // auto catches network / connection error
          // setIsPending(false);
          // setError(err.message);
        }
      })
  }, [page]);

  return (
    <div className="products-list clearfix">
      {data && data.map((row, key) => (
        <ProductRow key={key} products={row} />
      ))}
    </div>
  )
};

export default ProductsList;
