// ============================================================
// Calculator Logic — Drive Point Solutions
// ============================================================

// --- AUTO REFINANCE CALCULATOR ---

export interface AutoRefiInputs {
  currentPayment: number;
  balance: number;
  currentRate: number;
  newRate: number;
  currentTerm: number;
  newTerm: number;
}

export interface AutoRefiOutputs {
  newMonthlyPayment: number;
  monthlySavings: number;
  totalInterestSaved: number;
}

/**
 * Calcula el pago mensual usando la fórmula estándar de amortización (PMT).
 * @param principal - Saldo del préstamo ($)
 * @param annualRate - Tasa anual (ej: 4.5 para 4.5%)
 * @param months - Número de meses
 */
function calculatePMT(principal: number, annualRate: number, months: number): number {
  const monthlyRate = annualRate / 12 / 100;

  // Si la tasa es 0, el pago es simplemente el principal dividido entre los meses
  if (monthlyRate === 0) return parseFloat((principal / months).toFixed(2));

  // Fórmula: M = P [ i(1+i)^n ] / [ (1+i)^n - 1 ]
  const pow = Math.pow(1 + monthlyRate, months);
  const payment = principal * (monthlyRate * pow) / (pow - 1);

  return parseFloat(payment.toFixed(2));
}

export function calculateAutoRefinance(inputs: AutoRefiInputs): AutoRefiOutputs {
  const {
    currentPayment,
    balance,
    newRate,
    currentTerm,
    newTerm,
  } = inputs;

  // Convertir años a meses
  const currentMonths = currentTerm * 12;
  const newMonths = newTerm * 12;

  // 1. Nuevo pago mensual con las nuevas condiciones
  const newMonthlyPayment = calculatePMT(balance, newRate, newMonths);

  // 2. Ahorro mensual: diferencia entre el pago actual y el nuevo
  const monthlySavings = parseFloat((currentPayment - newMonthlyPayment).toFixed(2));

  // 3. Ahorro total en intereses:
  //    Intereses actuales = (pago actual × meses restantes) - saldo
  //    Intereses nuevos   = (nuevo pago  × nuevos meses)    - saldo
  const totalInterestOld = (currentPayment * currentMonths) - balance;
  const totalInterestNew = (newMonthlyPayment * newMonths) - balance;
  const totalInterestSaved = parseFloat((totalInterestOld - totalInterestNew).toFixed(2));

  return {
    newMonthlyPayment,
    monthlySavings,
    totalInterestSaved,
  };
}