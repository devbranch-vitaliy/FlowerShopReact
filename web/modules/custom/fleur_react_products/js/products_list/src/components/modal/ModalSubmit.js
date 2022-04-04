import React from 'react';
import {request} from "../../utilits/api";
import {dispatch} from "../../utilits/globals";

const ModalSubmit = () => {

  const addToCart = () => {
    request('add_to_cart')
      .then(order => {
        if (!order.length) {
          console.log('The request is successful but the order was not updated. Please contact admin.')
          return 0;
        }
        dispatch({type: 'hideCart'})

        // Go to the cart page.
        window.location.replace('/cart');
      })
      .catch(err => {
        console.log(err.message);
      })
  }

  return (
    <button
      className={'button--add-to-cart btn-success btn btn-default'}
      type={'submit'}
      onClick={addToCart}
    >
      Add to cart
    </button>
  );
}

export default ModalSubmit;
