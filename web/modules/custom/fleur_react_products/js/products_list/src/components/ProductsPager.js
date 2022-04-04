import React from "react";
import {dispatch, useGlobalState} from "../utilits/globals";

const ProductsPager = () => {
  const [pager] = useGlobalState("pager");
  const [isLoading] = useGlobalState("isLoading");

  return (
    <div className="pager" key={'pager'}>
      {pager &&
        <button className="load-more" onClick={() => dispatch({type: "showMore"})}>{isLoading ? 'Loading...' : 'Load more products'}</button>
      }
    </div>
  )
};

export default ProductsPager;
