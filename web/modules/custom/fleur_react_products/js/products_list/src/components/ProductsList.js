import React, {useEffect, useState} from "react";
import { request, attachRelationship } from "../utilits/api";
import ProductRow from "./ProductRow";

const ProductsList = () => {

  const [data, setData] = useState([]);
  const [page, setPage] = useState(0);
  const [count, setCount] = useState(0);
  const [isLoading, setIsLoading] = useState(false);
  const [errorMsg, setErrorMsg] = useState('');
  const perRow = 3;
  const perPage = perRow;

  useEffect(() => {
    setIsLoading(true);

    request('products_list', { page: page , perPage: perPage})
      .then(products => {
        let products_data = [];
        setCount(Number(products.meta.count));

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
        setIsLoading(false);
        setData([...data, ...products_data]);
      })
      .catch(err => {
        if (err.name === 'AbortError') {
          console.log('fetch aborted')
        } else {
          console.log(err.message);
        }
      })
  }, [page]);

  const loadMoreHandler = () => {
    setPage((page) => page + 1);
  };

  return (
    <div className="products-list-wrapper">
      <div className="products-list clearfix">
        {data && data.map((row, id) => (
          <ProductRow key={id} products={row} />
        ))}
      </div>
      {((page + 1) * perPage < count) && <div className="pager">
        <button className="load-more" onClick={loadMoreHandler}>{isLoading ? 'Loading...' : 'Load more products'}</button>
      </div>}
    </div>
  )
};

export default ProductsList;
