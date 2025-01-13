import Calendar from "react-calendar";
import "./Calendar.css"

interface CalendarProps {
  setDate: any;
  date: any;
}
function CalendarComponent(props: CalendarProps) {
  const { setDate, date } = props;
  const today = new Date();

  return (
      <Calendar 
        onChange={setDate} 
        value={date} 
        minDate={today}
      />
  );
};

export default CalendarComponent;