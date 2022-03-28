import React, {useEffect, useState} from "react";
import { request, findRelationship } from "../utilits/api";

const Page = () => {

  const [data, setData] = useState([]);
  const [page, setPage] = useState(0);
  const [isLoading, setIsLoading] = useState(false);
  const [errorMsg, setErrorMsg] = useState('');

  useEffect(() => {
    setIsLoading(true);

    request('products_list', { page: 0 })
      .then(products => {
        const products_data = [];
        products.data.map((product) => {
          products_data.push({
            title: product.attributes.title,
            default_variation: findRelationship(product.relationships.default_variation.data, products.included),
            field_image: findRelationship(product.relationships.field_image.data, products.included),
          });
        })
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
    <div className={"products-list"}>
      <p><strong>REACT</strong> There will be react widget here.</p>
    </div>
  )
};

export default Page;
