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

export interface MortgageInputs {
  homePrice: number;        // Precio de la casa ($)
  downPayment: number;      // Pago inicial ($)
  loanTermYears: number;    // Plazo del préstamo (años)
  annualRate: number;       // Tasa de interés anual (%)
  annualPropertyTax: number; // Impuesto predial anual ($)
  annualHomeInsurance: number; // Seguro de casa anual ($)
  monthlyPMI: number;       // PMI mensual ($, puede ser 0)
}

export interface MortgageOutputs {
  loanAmount: number;            // Monto del préstamo ($)
  principalAndInterest: number;  // Principal e interés mensual ($)
  monthlyPropertyTax: number;    // Impuesto predial mensual ($)
  monthlyHomeInsurance: number;  // Seguro de casa mensual ($)
  totalMonthlyPayment: number;   // Pago mensual total ($)
  totalInterest: number;         // Interés total pagado en toda la vida del préstamo ($)
  totalCost: number;             // Costo total (préstamo + intereses) ($)
}

export function calculateMortgage(inputs: MortgageInputs): MortgageOutputs {
  const {
    homePrice,
    downPayment,
    loanTermYears,
    annualRate,
    annualPropertyTax,
    annualHomeInsurance,
    monthlyPMI,
  } = inputs;

  const months = loanTermYears * 12;

  // 1. Monto del préstamo: lo que el banco financia
  const loanAmount = homePrice - downPayment;

  // 2. Principal e interés: fórmula PMT sobre el monto financiado
  const principalAndInterest = calculatePMT(loanAmount, annualRate, months);

  // 3. Impuesto predial y seguro: divididos entre 12 para cuota mensual
  const monthlyPropertyTax = parseFloat((annualPropertyTax / 12).toFixed(2));
  const monthlyHomeInsurance = parseFloat((annualHomeInsurance / 12).toFixed(2));

  // 4. Pago mensual total: suma de todos los componentes
  const totalMonthlyPayment = parseFloat(
    (principalAndInterest + monthlyPropertyTax + monthlyHomeInsurance + monthlyPMI).toFixed(2)
  );

  // 5. Interés total = (cuota P&I × meses) - monto del préstamo
  const totalInterest = parseFloat(
    ((principalAndInterest * months) - loanAmount).toFixed(2)
  );

  // 6. Costo total = monto del préstamo + todos los intereses
  const totalCost = parseFloat((loanAmount + totalInterest).toFixed(2));

  return {
    loanAmount,
    principalAndInterest,
    monthlyPropertyTax,
    monthlyHomeInsurance,
    totalMonthlyPayment,
    totalInterest,
    totalCost,
  };
}