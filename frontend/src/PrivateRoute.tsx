import { Navigate } from 'react-router-dom';
import { useSelector, useDispatch } from 'react-redux';
import { RootState, AppDispatch } from './store/store';
import { useEffect } from 'react';
import { fetchUser } from './store/authSlice';
import LoadingComponent from './components/loadingComponent/LoadingComponent';

interface PrivateRouteProps {
  children: JSX.Element;
}

export default function PrivateRoute({ children }: PrivateRouteProps) {
  const dispatch = useDispatch<AppDispatch>();
  const { isAuthenticated, loading } = useSelector((state: RootState) => state.auth);

  useEffect(() => {
    dispatch(fetchUser());
  }, [dispatch]);

  if (loading) return <LoadingComponent />;
  if (!isAuthenticated) return <Navigate to="/login" replace />;

  return children;
}
