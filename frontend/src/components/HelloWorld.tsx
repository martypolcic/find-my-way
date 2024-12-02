import { useState } from 'react'
import './HelloWorld.css'

function HelloWorld() {
  const [count, setCount] = useState(0);

  return (
    <>
      <div className='HelloWorld'>
        <h1>Hello World!</h1>

        <p>Count: {count}</p>
        <button type='button' onClick={() => setCount(count + 1)}>Increment</button>
      </div>
    </>
  )
}

export default HelloWorld
